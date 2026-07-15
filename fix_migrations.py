import os
import glob
import re

for filepath in glob.glob("database/migrations/*.php"):
    with open(filepath, 'r') as f:
        content = f.read()
    
    changed = False
    
    if "$table->integer('id_" in content:
        content = content.replace("$table->integer('id_", "$table->unsignedBigInteger('id_")
        changed = True
        
    if "default('FALSE')" in content:
        content = content.replace("default('FALSE')", "default(false)")
        changed = True
        
    if "default('TRUE')" in content:
        content = content.replace("default('TRUE')", "default(true)")
        changed = True

    if changed:
        with open(filepath, 'w') as f:
            f.write(content)

print("Replaced integer with unsignedBigInteger and boolean defaults in migrations.")
