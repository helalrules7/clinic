
import sqlite3

try:
    conn = sqlite3.connect('/var/www/html/clinic/--Docs/database_remote.sqlite')
    cursor = conn.cursor()
    
    cursor.execute("SELECT parent_id FROM items WHERE id = 39")
    result = cursor.fetchone()
    
    if result:
        print(f"Parent ID of ID 39 is: {result[0]}")
    else:
        print("Item 39 not found")
        
    conn.close()
except Exception as e:
    print(f"Error: {e}")
