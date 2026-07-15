import os
import re
import datetime

sql_file_path = '/Users/adjasambe/Downloads/imp_db.sql'

with open(sql_file_path, 'r', encoding='utf-8') as f:
    sql_content = f.read()

# Strip SQL comments
sql_content = re.sub(r'--.*', '', sql_content)

# Pattern to find CREATE TABLE blocks
table_pattern = re.compile(r'CREATE\s+TABLE\s+(\w+)\s*\((.*?)\);', re.IGNORECASE | re.DOTALL)
tables = table_pattern.findall(sql_content)

def to_camel_case(snake_str, pascal=True):
    components = snake_str.lower().split('_')
    if pascal:
        return ''.join(x.title() for x in components)
    else:
        return components[0] + ''.join(x.title() for x in components[1:])

type_mapping = {
    'SERIAL PRIMARY KEY': 'id',
    'VARCHAR': 'string',
    'TEXT': 'text',
    'DATE': 'date',
    'CHAR': 'char',
    'DECIMAL': 'decimal',
    'INT': 'integer',
    'BOOLEAN': 'boolean',
    'TIMESTAMP': 'timestamp',
}

migrations_dir = 'database/migrations'
models_dir = 'app/Models'
os.makedirs(migrations_dir, exist_ok=True)
os.makedirs(models_dir, exist_ok=True)

# Generate current timestamp for migrations
now = datetime.datetime.now()

dependencies_graph = {}
table_data_map = {}

# Analyze tables to sort them topologically
for table_name, columns_str in tables:
    table_name_lower = table_name.lower()
    columns_raw = [c.strip() for c in columns_str.split(',') if c.strip()]
    deps = []
    parsed_columns = []
    for col in columns_raw:
        if 'REFERENCES' in col:
            match = re.search(r'REFERENCES\s+(\w+)', col, re.IGNORECASE)
            if match:
                dep_table = match.group(1).lower()
                deps.append(dep_table)
        
        # Primary key inline like PRIMARY KEY (id_role, id_permission)
        if col.upper().startswith('PRIMARY KEY'):
            parsed_columns.append({'type': 'raw', 'content': f"$table->primary([{col.split('(')[1].split(')')[0]}]);"})
            continue
            
        col_parts = col.split()
        if len(col_parts) < 2: continue
        col_name = col_parts[0]
        col_type_raw = col_parts[1].upper()
        
        col_type = 'string'
        size = None
        for k, v in type_mapping.items():
            if k in col.upper():
                col_type = v
                break
        
        # Extract sizes e.g. VARCHAR(50) or DECIMAL(12, 2)
        if '(' in col_parts[1]:
            size = col_parts[1].split('(')[1].split(')')[0]

        is_nullable = 'NOT NULL' not in col.upper()
        if col_type == 'id':
            is_nullable = False
            
        is_unique = 'UNIQUE' in col.upper()
        default_val = None
        if 'DEFAULT' in col.upper():
            default_match = re.search(r'DEFAULT\s+([\w_]+)', col, re.IGNORECASE)
            if default_match:
                default_val = default_match.group(1)

        references = None
        if 'REFERENCES' in col.upper():
            ref_match = re.search(r'REFERENCES\s+(\w+)\((\w+)\)', col, re.IGNORECASE)
            on_delete = re.search(r'ON\s+DELETE\s+(\w+)', col, re.IGNORECASE)
            if ref_match:
                references = {
                    'table': ref_match.group(1).lower(),
                    'column': ref_match.group(2).lower(),
                    'on_delete': on_delete.group(1).lower() if on_delete else 'restrict'
                }

        parsed_columns.append({
            'name': col_name,
            'type': col_type,
            'size': size,
            'nullable': is_nullable,
            'unique': is_unique,
            'default': default_val,
            'references': references
        })
        
    dependencies_graph[table_name_lower] = deps
    table_data_map[table_name_lower] = {
        'original_name': table_name,
        'columns': parsed_columns
    }

# Sort topologically
sorted_tables = []
visited = set()
def visit(t):
    if t in visited: return
    for dep in dependencies_graph.get(t, []):
        visit(dep)
    visited.add(t)
    sorted_tables.append(t)

for t in dependencies_graph:
    visit(t)

# Generate files
for idx, table_key in enumerate(sorted_tables):
    table_data = table_data_map[table_key]
    table_name = table_data['original_name'].lower()
    model_name = to_camel_case(table_name)
    
    # MIGRATION
    ts = (now + datetime.timedelta(seconds=idx)).strftime('%Y_%m_%d_%H%M%S')
    mig_filename = f"{ts}_create_{table_name}_table.php"
    
    mig_content = f"<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration\n{{\n    public function up()\n    {{\n        Schema::create('{table_name}', function (Blueprint $table) {{\n"
    
    primary_key_col = 'id'
    
    for col in table_data['columns']:
        if col.get('type') == 'raw':
            pass # Handle composite PK later if needed
            continue
            
        cname = col['name']
        ctype = col['type']
        
        if ctype == 'id':
            primary_key_col = cname
            mig_content += f"            $table->id('{cname}');\n"
            continue
            
        size_str = f", {col['size']}" if col['size'] else ""
        if ctype == 'decimal' and size_str:
            size_str = f", {col['size'].replace(' ', '')}"
            
        if ctype == 'char' and size_str:
             mig_content += f"            $table->char('{cname}'{size_str})"
        elif ctype == 'string' and size_str:
             mig_content += f"            $table->string('{cname}'{size_str})"
        elif ctype == 'decimal' and size_str:
             mig_content += f"            $table->decimal('{cname}'{size_str})"
        else:
             if col.get('references') and ctype == 'integer':
                 mig_content += f"            $table->unsignedBigInteger('{cname}')"
             else:
                 mig_content += f"            $table->{ctype}('{cname}')"
             
        if col['nullable']: mig_content += "->nullable()"
        if col['unique']: mig_content += "->unique()"
        if col['default'] and col['default'] not in ['CURRENT_DATE', 'CURRENT_TIMESTAMP']: 
            mig_content += f"->default('{col['default']}')"
            
        mig_content += ";\n"
        
        # foreign key
        if col['references']:
            ref = col['references']
            mig_content += f"            $table->foreign('{cname}')->references('{ref['column']}')->on('{ref['table']}')->onDelete('{ref['on_delete']}');\n"

    # Some defaults
    mig_content += "            $table->timestamps();\n"
    mig_content += "        });\n    }\n\n    public function down()\n    {{\n        Schema::dropIfExists('{table_name}');\n    }\n}};\n"
    
    with open(os.path.join(migrations_dir, mig_filename), 'w') as mf:
        mf.write(mig_content)
        
    # MODEL
    model_content = f"<?php\n\nnamespace App\\Models;\n\nuse Illuminate\\Database\\Eloquent\\Model;\n\nclass {model_name} extends Model\n{{\n"
    model_content += f"    protected $table = '{table_name}';\n"
    model_content += f"    protected $primaryKey = '{primary_key_col}';\n"
    model_content += "    protected $guarded = [];\n\n"
    
    # Relations (BelongsTo)
    for col in table_data['columns']:
        if col.get('references'):
            rel_table = col['references']['table']
            rel_model = to_camel_case(rel_table)
            rel_name = to_camel_case(rel_table, False)
            model_content += f"    public function {rel_name}()\n    {{\n        return $this->belongsTo({rel_model}::class, '{col['name']}');\n    }}\n\n"
            
    model_content += "}\n"
    
    with open(os.path.join(models_dir, f"{model_name}.php"), 'w') as modf:
        modf.write(model_content)

print("Architecture successfully generated!")
