import sys
import pymupdf
from pandukabhaya import Converter

document=pymupdf.open(sys.argv[1])
converter=Converter('fm_abhaya')
for value in sys.argv[2:]:
    page=int(value)
    print(f'\n--- PAGE {page} ---')
    print(converter.convert(document[page-1].get_text())[:5000])
