#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sqlite3
import os

# Get the directory of this script
script_dir = os.path.dirname(os.path.abspath(__file__))
db_path = os.path.join(script_dir, 'database.sqlite')

# Read content files
content_en_path = os.path.join(script_dir, 'content', 'getting-started-en.html')
content_ar_path = os.path.join(script_dir, 'content', 'getting-started-ar.html')

try:
    with open(content_en_path, 'r', encoding='utf-8') as f:
        content_en = f.read()
    
    with open(content_ar_path, 'r', encoding='utf-8') as f:
        content_ar = f.read()
    
    # Connect to database
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    
    # Update English translation
    cursor.execute("""
        UPDATE translations 
        SET content = ? 
        WHERE item_id = 1 AND locale = 'en'
    """, (content_en,))
    
    # Update Arabic translation
    cursor.execute("""
        UPDATE translations 
        SET content = ? 
        WHERE item_id = 1 AND locale = 'ar'
    """, (content_ar,))
    
    conn.commit()
    conn.close()
    
    print("✓ Successfully updated Getting Started content!")
    print("  - English content updated")
    print("  - Arabic content updated")
    
except Exception as e:
    print(f"✗ Error: {e}")
    exit(1)
