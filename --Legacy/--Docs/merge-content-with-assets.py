#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sqlite3
import os

# Get the directory of this script
script_dir = os.path.dirname(os.path.abspath(__file__))
db_path = os.path.join(script_dir, 'database.sqlite')

# Read CSS (Getting Started styles only - from line 808 to 1544)
css_path = os.path.join(script_dir, 'public', 'assets', 'css', 'style.css')
with open(css_path, 'r', encoding='utf-8') as f:
    all_css = f.readlines()
    # Extract Getting Started styles (lines 808-1544, 0-indexed: 807-1543)
    getting_started_css = ''.join(all_css[807:1544])

# Read JS (Getting Started scripts only - from line 108 to 263)
js_path = os.path.join(script_dir, 'public', 'assets', 'js', 'app.js')
with open(js_path, 'r', encoding='utf-8') as f:
    all_js = f.readlines()
    # Extract Getting Started scripts (lines 108-263, 0-indexed: 107-262)
    getting_started_js = ''.join(all_js[107:263])

# Read HTML content files
content_en_path = os.path.join(script_dir, 'content', 'getting-started-en.html')
content_ar_path = os.path.join(script_dir, 'content', 'getting-started-ar.html')

with open(content_en_path, 'r', encoding='utf-8') as f:
    html_en = f.read()

with open(content_ar_path, 'r', encoding='utf-8') as f:
    html_ar = f.read()

# Merge: HTML + CSS + JS
merged_en = f"""<style>
{getting_started_css}
</style>

{html_en}

<script>
{getting_started_js}
</script>"""

merged_ar = f"""<style>
{getting_started_css}
</style>

{html_ar}

<script>
{getting_started_js}
</script>"""

# Update database
try:
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    
    # Update English translation
    cursor.execute("""
        UPDATE translations 
        SET content = ? 
        WHERE item_id = 1 AND locale = 'en'
    """, (merged_en,))
    
    # Update Arabic translation
    cursor.execute("""
        UPDATE translations 
        SET content = ? 
        WHERE item_id = 1 AND locale = 'ar'
    """, (merged_ar,))
    
    conn.commit()
    
    # Verify
    cursor.execute("SELECT locale, LENGTH(content) as content_length FROM translations WHERE item_id = 1")
    rows = cursor.fetchall()
    
    print("✓ Successfully merged CSS and JS into content!")
    print("\nContent sizes:")
    for row in rows:
        print(f"  {row[0]}: {row[1]} bytes")
    
    conn.close()
    
except Exception as e:
    print(f"✗ Error: {e}")
    exit(1)
