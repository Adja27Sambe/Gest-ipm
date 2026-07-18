import os
import re

MIGRATIONS_DIR = 'database/migrations'
MODELS_DIR = 'app/Models'

def to_camel_case(snake_str):
    components = snake_str.split('_')
    return components[0] + ''.join(x.title() for x in components[1:])

def to_pascal_case(snake_str):
    components = snake_str.split('_')
    return ''.join(x.title() for x in components)

def pluralize(word):
    if word.endswith('s'):
        return word
    return word + 's'

def add_relation_to_model(model_name, relation_code, imports):
    model_path = os.path.join(MODELS_DIR, f"{model_name}.php")
    if not os.path.exists(model_path):
        print(f"Model {model_name} not found.")
        return

    with open(model_path, 'r') as f:
        content = f.read()

    # Check if relation already exists (crude check)
    func_name_match = re.search(r'function\s+(\w+)\s*\(', relation_code)
    if func_name_match:
        func_name = func_name_match.group(1)
        if f"function {func_name}(" in content or f"function {func_name} (" in content:
            print(f"Relation {func_name} already exists in {model_name}.")
            return

    # Add imports if not present
    for imp in imports:
        if imp not in content:
            content = content.replace('use Illuminate\\Database\\Eloquent\\Model;', f"use Illuminate\\Database\\Eloquent\\Model;\n{imp}")

    # Add relation method before the last closing brace
    last_brace_index = content.rfind('}')
    if last_brace_index != -1:
        content = content[:last_brace_index] + "\n" + relation_code + "\n}\n"
        with open(model_path, 'w') as f:
            f.write(content)
        print(f"Added relation to {model_name}.")

def main():
    for filename in os.listdir(MIGRATIONS_DIR):
        if not filename.endswith('.php'):
            continue
        
        filepath = os.path.join(MIGRATIONS_DIR, filename)
        with open(filepath, 'r') as f:
            content = f.read()
        
        table_match = re.search(r"Schema::create\('([^']+)'", content)
        if not table_match:
            continue
        table_name = table_match.group(1)
        local_model = to_pascal_case(table_name)
        
        # Find foreign keys: $table->foreign('local_key')->references('foreign_key')->on('foreign_table')
        fks = re.findall(r"\$table->foreign\('([^']+)'\)->references\('([^']+)'\)->on\('([^']+)'\)", content)
        
        for local_key, foreign_key, foreign_table in fks:
            foreign_model = to_pascal_case(foreign_table)
            
            # belongsTo in local_model
            rel_name = local_key.replace('id_', '')
            if rel_name == local_key: # no id_ prefix
                rel_name = foreign_table
            
            rel_name = to_camel_case(rel_name)
            
            belongs_to_code = f"""    public function {rel_name}(): \\Illuminate\\Database\\Eloquent\\Relations\\BelongsTo
    {{
        return $this->belongsTo({foreign_model}::class, '{local_key}', '{foreign_key}');
    }}"""
            add_relation_to_model(local_model, belongs_to_code, ['use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;'])
            
            # hasMany in foreign_model
            has_many_rel = pluralize(to_camel_case(table_name))
            has_many_code = f"""    public function {has_many_rel}(): \\Illuminate\\Database\\Eloquent\\Relations\\HasMany
    {{
        return $this->hasMany({local_model}::class, '{local_key}', '{foreign_key}');
    }}"""
            add_relation_to_model(foreign_model, has_many_code, ['use Illuminate\\Database\\Eloquent\\Relations\\HasMany;'])

if __name__ == '__main__':
    main()
