import re

with open('/Users/adjasambe/Downloads/imp_db.sql', 'r', encoding='utf-8') as f:
    sql = f.read()

# Replace SERIAL PRIMARY KEY with INT AUTO_INCREMENT PRIMARY KEY
sql = re.sub(r'SERIAL PRIMARY KEY', 'INT AUTO_INCREMENT PRIMARY KEY', sql, flags=re.IGNORECASE)

# PostgreSQL uses RESTRICT by default or explicitly. MySQL supports it.
# PostgreSQL uses SET NULL. MySQL supports it.
# PostgreSQL CASCADE. MySQL supports it.

# Write the converted SQL
with open('/Users/adjasambe/Downloads/mysql_imp_db.sql', 'w', encoding='utf-8') as f:
    f.write("CREATE DATABASE IF NOT EXISTS gest_ipm;\n")
    f.write("USE gest_ipm;\n\n")
    f.write(sql)

print("Converted SQL written to mysql_imp_db.sql")
