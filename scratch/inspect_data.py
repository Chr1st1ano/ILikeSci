import sqlite3
import os

db_path = 'ilikesci_db.sqlite'
if os.path.exists(db_path):
    conn = sqlite3.connect(db_path)
    c = conn.cursor()
    tables = [r[0] for r in c.execute("SELECT name FROM sqlite_master WHERE type='table'").fetchall()]
    print("SQLite Tables:", tables)
    for t in tables:
        count = c.execute(f"SELECT count(*) FROM {t}").fetchone()[0]
        print(f"Table '{t}': {count} rows")
        if 'student' in t or 'record' in t or 'grade' in t:
            sample = c.execute(f"SELECT * FROM {t} LIMIT 5").fetchall()
            cols = [d[0] for d in c.description]
            print(f"  Columns: {cols}")
            for row in sample:
                print(f"  Sample row: {row}")

# Check MySQL
import pymysql
try:
    mconn = pymysql.connect(host='127.0.0.1', port=3306, user='root', password='')
    print("MySQL is RUNNING and connected!")
    with mconn.cursor() as mc:
        mc.execute("SHOW DATABASES LIKE 'ilikesci%'")
        print("MySQL DBs:", mc.fetchall())
except Exception as e:
    print("MySQL not connected:", e)
