"""Extract normalized Unicode text from an admin-uploaded curriculum PDF."""
import json,re,sys
from pathlib import Path
from pypdf import PdfReader
from pandukabhaya import Converter
sys.stdout.reconfigure(encoding='utf-8')
path=Path(sys.argv[1]);encoding=sys.argv[2] if len(sys.argv)>2 else 'auto';reader=PdfReader(path);converter=Converter('fm_abhaya');pages=[]
for number,page in enumerate(reader.pages,1):
 raw=page.extract_text() or ''
 legacy=encoding=='fm_abhaya' or (encoding=='auto' and not re.search(r'[\u0d80-\u0dff]',raw) and bool(re.search(r'laIq|fY%|úoHd|m%Yak|ms<s;=re',raw)))
 text=converter.convert(raw) if legacy else raw;text=re.sub(r'[ \t]+',' ',text);text=re.sub(r'\n{3,}','\n\n',text).strip()
 if text:pages.append({'page':number,'text':text})
print(json.dumps({'pages':len(reader.pages),'extracted_pages':len(pages),'text':'\n\n'.join(x['text'] for x in pages)},ensure_ascii=False))
