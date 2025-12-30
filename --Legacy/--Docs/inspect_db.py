import sqlite3

def inspect_schema():
    conn = sqlite3.connect('database.sqlite')
    cursor = conn.cursor()
    cursor.execute("PRAGMA table_info(translations)")
    columns = cursor.fetchall()
    print("translations table schema:")
    for col in columns:
        print(col)
    conn.close()

if __name__ == "__main__":
    inspect_schema()
