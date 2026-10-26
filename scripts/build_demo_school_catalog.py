"""Build a geographically distributed demo catalog from the official MOE XLSX.

Source: https://dmb.moe.gov.lk/2024/01/01_Excel/Tbl20240101.xlsx
Input is downloaded to backups/sri-lanka-national-schools-2024.xlsx.
"""
import collections
import json
from pathlib import Path
import xml.etree.ElementTree as ET
import zipfile

base = Path(__file__).resolve().parents[1]
ns = {'m': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
with zipfile.ZipFile(base / 'backups/sri-lanka-national-schools-2024.xlsx') as archive:
    strings = [''.join(row.itertext()) for row in ET.fromstring(archive.read('xl/sharedStrings.xml')).findall('m:si', ns)]
    sheet = ET.fromstring(archive.read('xl/worksheets/sheet1.xml'))
    rows = []
    for row in sheet.findall('.//m:sheetData/m:row', ns):
        values = {}
        for cell in row.findall('m:c', ns):
            value = cell.find('m:v', ns)
            values[''.join(c for c in cell.attrib['r'] if c.isalpha())] = (
                strings[int(value.text)] if cell.get('t') == 's' else value.text
            ) if value is not None else ''
        if values.get('A', '').isdigit() and values.get('B', '').isdigit():
            rows.append(values)
groups = collections.defaultdict(collections.deque)
for row in rows:
    medium = row['L'].split('.', 1)[-1]
    mediums = [{'S': 'Sinhala', 'T': 'Tamil', 'E': 'English'}[code] for code in medium.split('/')]
    district = row['F'].split('.', 1)[-1].strip()
    # Include only schools offering the secondary grades represented by this app.
    if row['J'].split('.', 1)[-1].strip() not in ('Grd 1-13', 'Grd 6-13', 'Grd 1-11', 'Grd 6-11'):
        continue
    groups[district].append({'census_number': int(row['B']), 'name': row['C'].strip(), 'district': district, 'mediums': mediums})
selected = []
while len(selected) < 291:
    progressed = False
    for group in groups.values():
        if group and len(selected) < 291:
            selected.append(group.popleft())
            progressed = True
    if not progressed:
        raise RuntimeError('Not enough secondary schools')
assert len({row['name'] for row in selected}) == 291, 'Duplicate school names need disambiguation'
catalog = {'source': 'https://dmb.moe.gov.lk/2024/01/01_Excel/Tbl20240101.xlsx', 'year': 2024, 'schools': selected}
destination = base / 'database/data/sri_lanka_demo_schools.json'
destination.write_text(json.dumps(catalog, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
print(f'Saved {len(selected)} official schools across {len(groups)} districts to {destination.name}')
