#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Excel Analysis Script for Klara POS Integration
Analyzes Artikel_Export.xlsx and Delivery_Export.xlsx
"""

import pandas as pd
import json
from pathlib import Path

# Paths
BASE_DIR = Path(__file__).parent
DATABASE_DIR = BASE_DIR / "database"
ARTIKEL_FILE = DATABASE_DIR / "Artikel_Export.xlsx"
DELIVERY_FILE = DATABASE_DIR / "Delivery_Export_from_1_to_43.xlsx"

def analyze_excel_files():
    """Analyze both Excel files and print structure"""

    print("=" * 80)
    print("KLARA POS DATA ANALYSIS")
    print("=" * 80)
    print()

    # Analyze Artikel Export
    if ARTIKEL_FILE.exists():
        print(f"📊 Analyzing: {ARTIKEL_FILE.name}")
        print("-" * 80)

        try:
            # Read Excel file
            df_artikel = pd.read_excel(ARTIKEL_FILE)

            print(f"\n✓ Total rows: {len(df_artikel)}")
            print(f"✓ Total columns: {len(df_artikel.columns)}")
            print(f"\nColumn Names:")
            for i, col in enumerate(df_artikel.columns, 1):
                print(f"  {i}. {col}")

            print(f"\n\nFirst 5 rows preview:")
            print(df_artikel.head().to_string())

            # Save column info to JSON
            col_info = {
                "file": "Artikel_Export.xlsx",
                "total_rows": len(df_artikel),
                "total_columns": len(df_artikel.columns),
                "columns": list(df_artikel.columns),
                "sample_data": df_artikel.head(3).to_dict('records')
            }

            with open(DATABASE_DIR / "artikel_analysis.json", "w", encoding="utf-8") as f:
                json.dump(col_info, f, ensure_ascii=False, indent=2, default=str)

            print(f"\n✓ Saved analysis to: artikel_analysis.json")

        except Exception as e:
            print(f"❌ Error reading Artikel file: {e}")

    print("\n" + "=" * 80)
    print()

    # Analyze Delivery Export
    if DELIVERY_FILE.exists():
        print(f"📊 Analyzing: {DELIVERY_FILE.name}")
        print("-" * 80)

        try:
            df_delivery = pd.read_excel(DELIVERY_FILE)

            print(f"\n✓ Total rows: {len(df_delivery)}")
            print(f"✓ Total columns: {len(df_delivery.columns)}")
            print(f"\nColumn Names:")
            for i, col in enumerate(df_delivery.columns, 1):
                print(f"  {i}. {col}")

            print(f"\n\nFirst 5 rows preview:")
            print(df_delivery.head().to_string())

            # Save column info to JSON
            col_info = {
                "file": "Delivery_Export_from_1_to_43.xlsx",
                "total_rows": len(df_delivery),
                "total_columns": len(df_delivery.columns),
                "columns": list(df_delivery.columns),
                "sample_data": df_delivery.head(3).to_dict('records')
            }

            with open(DATABASE_DIR / "delivery_analysis.json", "w", encoding="utf-8") as f:
                json.dump(col_info, f, ensure_ascii=False, indent=2, default=str)

            print(f"\n✓ Saved analysis to: delivery_analysis.json")

        except Exception as e:
            print(f"❌ Error reading Delivery file: {e}")

    print("\n" + "=" * 80)
    print("✓ Analysis complete!")
    print("=" * 80)

if __name__ == "__main__":
    analyze_excel_files()
