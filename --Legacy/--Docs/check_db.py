import sqlite3

try:
    conn = sqlite3.connect('/var/www/html/clinic/--Docs/database_remote.sqlite')
    cursor = conn.cursor()

    print("--- Columns in items table ---")
    cursor.execute("PRAGMA table_info(items)")
    columns = cursor.fetchall()
    for col in columns:
        print(col)

    print("\n--- Existing items (first 50) ---")
    cursor.execute("SELECT * FROM items LIMIT 50")
    rows = cursor.fetchall()
    for row in rows:
        print(row)

    print("\n--- Searching for Secretary parent ---")
    # Assuming the translations table links to these items, or I can guess based on known IDs.
    # Let's see if we can find 'Secretary' related items in translations to map back to items.
    
    # First check if translations table exists and has data
    cursor.execute("PRAGMA table_info(translations)")
    print("\n--- Columns in translations table ---")
    trans_cols = cursor.fetchall()
    for col in trans_cols:
        print(col)
        
    cursor.execute("SELECT * FROM translations WHERE title LIKE '%Secretary%' OR title LIKE '%السكرتير%'")
    print("\n--- Secretary Translations ---")
    sec_rows = cursor.fetchall()
    for row in sec_rows:
        print(row)

    conn.close()
except Exception as e:
    print(f"Error: {e}")
