import sqlite3
import os

# Configuration
DB_PATH = 'database.sqlite'
FILES_TO_UPDATE = [
    {
        'item_id': 39,
        'file_path': 'content/doctors-calendar-ar.html',
        'locale': 'ar',
        'title': 'تقويم الطبيب'
    },
    {
        'item_id': 39,
        'file_path': 'content/doctors-calendar-en.html',
        'locale': 'en',
        'title': "Doctor's Calendar"
    },
    {
        'item_id': 40,
        'file_path': 'content/secretary-calendar-ar.html',
        'locale': 'ar',
        'title': 'تقويم السكرتير'
    },
    {
        'item_id': 40,
        'file_path': 'content/secretary-calendar-en.html',
        'locale': 'en',
        'title': 'Secretary Calendar'
    },
    {
        'item_id': 42,
        'file_path': 'content/patients-ar.html',
        'locale': 'ar',
        'title': 'المرضى'
    },
    {
        'item_id': 42,
        'file_path': 'content/patients-en.html',
        'locale': 'en',
        'title': 'Patients'
    },
    {
        'item_id': 41,
        'file_path': 'content/login-ar.html',
        'locale': 'ar',
        'title': 'تسجيل الدخول'
    },
    # Financial Management (Item 46)
    {
        'item_id': 46,
        'file_path': 'content/financial-mgmt-ar.html',
        'locale': 'ar',
        'title': 'الإدارة المالية'
    },
    {
        'item_id': 46,
        'file_path': 'content/financial-mgmt-en.html',
        'locale': 'en',
        'title': 'Financial Management'
    },
    {
        'item_id': 43,
        'file_path': 'content/discussion-forum-ar.html',
        'locale': 'ar',
        'title': 'منتدى النقاش'
    },
    {
        'item_id': 43,
        'file_path': 'content/discussion-forum-en.html',
        'locale': 'en',
        'title': 'Discussion Forum'
    },
    {
        'item_id': 44,
        'file_path': 'content/patients-secretary-ar.html',
        'locale': 'ar',
        'title': 'إدارة المرضي - واجهة السكرتارية'
    },
    {
        'item_id': 44,
        'file_path': 'content/patients-secretary-en.html',
        'locale': 'en',
        'title': 'Patients Management - Secretary View'
    },
    {
        'item_id': 45,
        'file_path': 'content/drugs-db-ar.html',
        'locale': 'ar',
        'title': 'قاعدة بيانات الأدوية'
    },
    {
        'item_id': 45,
        'file_path': 'content/drugs-db-en.html',
        'locale': 'en',
        'title': 'Drugs Database'
    }
]

def update_translations():
    if not os.path.exists(DB_PATH):
        print(f"Error: Database file '{DB_PATH}' not found.")
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
                print(f"Warning: File '{file_path}' not found. Skipping item {item_id}.")
                continue

            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()

            # Delete existing translation to ensure clean slate
            print(f"Deleting existing translation for item {item_id} ({locale})...")
            cursor.execute(
                "DELETE FROM translations WHERE item_id = ? AND locale = ?",
                (item_id, locale)
            )
            
            # Insert new translation
            print(f"Inserting new translation for item {item_id} ({locale})...")
            cursor.execute(
                """
                INSERT INTO translations (item_id, locale, title, content)
                VALUES (?, ?, ?, ?)
                """,
                (item_id, locale, title, content)
            )
            
            # Also update the title in the items table if it's the default locale (optional, but good for consistency)
            # Assuming 'ar' might be the default or we just want to ensure the item has a title
            # But items table usually has a specific column structure. Let's check if we need to update items table.
            # Usually items table has a title column too? Or just relies on translations?

            # Based on previous exploration, translations table holds the content.
            
        # Update Items Table
        # Check if item 44 exists
        print("Checking items table for Item 44...")
        cursor.execute("SELECT id FROM items WHERE id = 44")
        item_44 = cursor.fetchone()
        
        if not item_44:
            print("Item 44 not found in items table. Inserting...")
            # Parent ID 38 (Secretary/Docs), Type 'sub', Sort Order 5 (after 4), Icon 'ri-group-line'
            cursor.execute(
                """
                INSERT INTO items (id, parent_id, type, sort_order, icon)
                VALUES (44, 38, 'sub', 5, 'ri-group-line')
                """
            )
            print("Successfully inserted item 44.")
        else:
            print("Item 44 already exists in items table.")
            

        # Check if item 45 exists (Drugs Database)
        print("Checking items table for Item 45...")
        cursor.execute("SELECT id FROM items WHERE id = 45")
        if not cursor.fetchone():
            print("Item 45 not found in items table. Inserting...")
            cursor.execute("""
                INSERT INTO items (id, parent_id, type, sort_order, icon)
                VALUES (45, 38, 'doc', 6, 'ri-medicine-bottle-line')
            """)
            print("Inserted Item ID 45 (Drugs Database) into items table.")
        else:
            print("Item 45 already exists in items table.")

        # Check if item 46 exists (Financial Management)
        print("Checking items table for Item 46...")
        cursor.execute("SELECT id FROM items WHERE id = 46")
        if not cursor.fetchone():
            print("Item 46 not found in items table. Inserting...")
            cursor.execute("""
                INSERT INTO items (id, parent_id, type, sort_order, icon)
                VALUES (46, 38, 'doc', 7, 'ri-money-dollar-circle-line')
            """)
            print("Inserted Item ID 46 (Financial Management) into items table.")
        else:
            print("Item 46 already exists in items table.")
            
        conn.commit()
        print("Successfully updated database translations and items.")

    except sqlite3.Error as e:
        print(f"Database error: {e}")
        conn.rollback()
    except Exception as e:
        print(f"Error: {e}")
        conn.rollback()

    print("Successfully updated database translations and items.")

    conn.close()

if __name__ == "__main__":
    update_translations()
