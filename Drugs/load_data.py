#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import csv
import mysql.connector
import sys

def load_drug_data():
    try:
        # الاتصال بقاعدة البيانات
        conn = mysql.connector.connect(
            host='localhost',
            user='AhmedHelal_egyptian_drugs',
            password='Carmen@1230',
            database='AhmedHelal_egyptian_drugs',
            charset='utf8mb4'
        )
        cursor = conn.cursor()
        
        print("بدء تحميل البيانات...")
        
        # قراءة ملف CSV وإدراج البيانات
        with open('drugs_export.csv', 'r', encoding='utf-8', errors='ignore') as file:
            csv_reader = csv.reader(file)
            next(csv_reader)  # تخطي العنوان
            
            count = 0
            errors = 0
            
            for row_num, row in enumerate(csv_reader, start=2):
                if len(row) >= 11:  # التأكد من وجود جميع الأعمدة
                    try:
                        # تقليم البيانات لتتناسب مع حجم الأعمدة
                        processed_row = [
                            row[0] if row[0] else None,  # ID
                            row[1][:86] if row[1] else None,  # FirstName
                            row[2][:100] if row[2] else None,  # LastName
                            row[3][:100] if row[3] else None,  # price
                            row[4][:100] if row[4] else None,  # priceold
                            row[5][:30] if row[5] else None,  # imageid
                            row[6][:54] if row[6] else None,  # Company
                            row[7][:96] if row[7] else None,  # Pharmacology
                            row[8][:60] if row[8] else None,  # SRDE
                            row[9][:1000] if row[9] else None,  # GI
                            row[10][:100] if row[10] else None,  # Route
                        ]
                        
                        cursor.execute('''
                            INSERT INTO drugs (ID, FirstName, LastName, price, priceold, imageid, Company, Pharmacology, SRDE, GI, Route)
                            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                        ''', processed_row)
                        
                        count += 1
                        if count % 1000 == 0:
                            print(f'تم تحميل {count} سجل...')
                            
                    except Exception as e:
                        errors += 1
                        if errors <= 10:  # طباعة أول 10 أخطاء فقط
                            print(f'خطأ في السطر {row_num}: {e}')
                        continue
                else:
                    errors += 1
                    if errors <= 10:
                        print(f'سطر {row_num}: عدد الأعمدة غير كافي ({len(row)})')
        
        conn.commit()
        cursor.close()
        conn.close()
        
        print(f'تم تحميل {count} سجل بنجاح!')
        print(f'عدد الأخطاء: {errors}')
        
    except Exception as e:
        print(f'خطأ عام: {e}')
        sys.exit(1)

if __name__ == "__main__":
    load_drug_data()
