import sqlite3
import sys

DB_PATH = 'database.sqlite'

def verify_translation(item_id, locale):
    try:
        conn = sqlite3.connect(DB_PATH)
        cursor = conn.cursor()
        cursor.execute(
            "SELECT length(content), title FROM translations WHERE item_id = ? AND locale = ?",
            (item_id, locale)
        )
        row = cursor.fetchone()
        if row:
            print(f"Item {item_id} ({locale}): Title='{row[1]}', Content Length={row[0]}")
            # Print snippet
            conn.text_factory = str
            cursor.execute("SELECT content FROM translations WHERE item_id = ? AND locale = ?", (item_id, locale))
            content = cursor.fetchone()[0]
            print(f"Snippet: {content[:100]}...")
        else:
            print(f"Item {item_id} ({locale}): NOT FOUND")
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    print("--- Verifying Remote Translations ---")
    verify_translation(83, 'ar') # Patients
    verify_translation(77, 'ar') # Doctor Calendar
    verify_translation(79, 'ar') # Secretary Calendar
