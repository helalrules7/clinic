import sqlite3
import os

# Configuration
DB_PATH = 'database.sqlite'
# Assuming the script runs in the root (--Docs) directory
# and content/ folder is there.
FILES_TO_UPDATE = [
    {
        'item_id': 77,
        'file_path': 'content/doctors-calendar-ar.html',
        'locale': 'ar',
        'title': 'تقويم الطبيب'
    },
    {
        'item_id': 79,
        'file_path': 'content/secretary-calendar-ar.html',
        'locale': 'ar',
        'title': 'تقويم السكرتير'
    },
    {
        'item_id': 83,
        'file_path': 'content/patients-ar.html',
        'locale': 'ar',
        'title': 'المرضى'
    }
]

def update_translations():
    print("Starting remote database update...")
    if not os.path.exists(DB_PATH):
        print(f"Error: Database file '{DB_PATH}' not found in {os.getcwd()}")
        return

    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()

    try:
        for item in FILES_TO_UPDATE:
            item_id = item['item_id']
            file_path = item['file_path']
            locale = item['locale']
            title = item['title']

            if not os.path.exists(file_path):
                print(f"Warning: File '{file_path}' not found content dir. CWD: {os.getcwd()}")
                # Try absolute path fallback if needed, but relative should work
                continue

            print(f"Reading file: {file_path}")
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()

            print(f"Content length for {item_id}: {len(content)} bytes")

            # Check if translation exists
            cursor.execute(
                "SELECT id FROM translations WHERE item_id = ? AND locale = ?",
                (item_id, locale)
            )
            row = cursor.fetchone()

            if row:
                # Update existing translation
                print(f"Updating translation for item {item_id} ({locale})...")
                cursor.execute(
                    """
                    UPDATE translations 
                    SET content = ?, title = ?
                    WHERE item_id = ? AND locale = ?
                    """,
                    (content, title, item_id, locale)
                )
            else:
                # Insert new translation
                print(f"Inserting new translation for item {item_id} ({locale})...")
                cursor.execute(
                    """
                    INSERT INTO translations (item_id, locale, title, content)
                    VALUES (?, ?, ?, ?)
                    """,
                    (item_id, locale, title, content)
                )
            
        conn.commit()
        print("Successfully updated database translations.")

    except sqlite3.Error as e:
        print(f"Database error: {e}")
        conn.rollback()
    except Exception as e:
        print(f"Error: {e}")
        conn.rollback()
    finally:
        conn.close()

if __name__ == "__main__":
    update_translations()
