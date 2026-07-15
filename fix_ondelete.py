import os
import glob

for filepath in glob.glob("database/migrations/*.php"):
    with open(filepath, 'r') as f:
        content = f.read()
    if "onDelete('set')" in content:
        content = content.replace("onDelete('set')", "onDelete('set null')")
        with open(filepath, 'w') as f:
            f.write(content)
print("Replaced onDelete('set') with onDelete('set null') in migrations.")
