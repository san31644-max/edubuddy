"""Build Unicode Tamil Grade 6 textbook lesson caches from the supplied PDFs."""
import json,re
from pathlib import Path
from pypdf import PdfReader
from pandukabhaya import Converter

ROOT=Path(__file__).resolve().parents[1]
BOOKS=ROOT/'uploads'/'syllabus'/'grade 6 tamil medium'
TARGET=ROOT/'uploads'/'syllabus'/'textbook-cache'
MAP=ROOT/'tools'/'AnyTaFont2UTF8'/'plugins'/'Release-x64'/'font_maps'/'shreelipi'
MAPPING=[line.split(' ',1) for line in MAP.read_text(encoding='utf-8').splitlines() if ' ' in line]
SINHALA_CONVERTER=Converter('fm_abhaya')

SOURCES=[
('civic','Civic Education','civic g-6 T.pdf',10,[(1,'எமது பாடசாலை',1),(2,'நாம் வாழும் பிரதேசம்',52),(3,'நற்பிரஜை',77)]),
('health','Health and Physical Education','Helth & PT G-06.pdf',12,[(1,'சுகமாக மகிழ்ச்சியாக வாழ்வோம்',1),(2,'தேவைகளையும் விருப்பங்களையும் இனங்காண்போம்',40),(3,'சரியான உடல் நிலைகளால் ஆளுமையை வளர்ப்போம்',48),(4,'ஓய்வு நேரத்தை மகிழ்ச்சியாகக் கழிக்க விளையாடுவோம்',63),(5,'தடகளத் திறன்களை வளர்ப்போம்',80),(6,'விதிகளை மதித்து விளையாடுவோம்',91),(7,'ஆரோக்கிய வாழ்வுக்குச் சரியான உணவுப் பழக்கங்கள்',97),(8,'எமது உடலை ஆரோக்கியமாக வைத்திருப்போம்',111),(9,'சமநிலை வாழ்வுக்கு உடற்தகுதியை வளர்ப்போம்',122),(10,'அறிவுடன் சவால்களை வெல்வோம்',136)]),
('history','History','History G 6.pdf',10,[(1,'வரலாறு ஓர் அறிமுகம்',1),(2,'ஆதி மனிதன்',8),(3,'உலகின் புராதன நாகரிகங்கள்',24),(4,'இலங்கையில் குடியேற்றங்கள் நிறுவப்படல்',38),(5,'எமது பண்டைய அரசர்கள்',45)]),
('mathematics','Mathematics','maths g-6 p-I T.pdf',12,[(1,'வட்டம்',1),(2,'இடப்பெறுமானம்',8),(3,'முழு எண்களின் கணிதச் செயல்கள்',21),(4,'நேரம்',38),(5,'எண் கோடு',61),(6,'மதிப்பீடும் முழுமையாக்கலும்',74),(7,'கோணங்கள்',84),(8,'திசைகள்',94),(9,'பின்னங்கள்',110),(10,'தெரிவு',132),(11,'காரணிகளும் மடங்குகளும்',137)]),
('mathematics','Mathematics','maths g-6 p-II T.pdf',12,[(12,'எளிய நேர்கோட்டுத் தள உருவங்கள்',1),(13,'தசமங்கள்',10),(14,'எண் வகைகளும் எண் கோலங்களும்',25),(15,'நீளம்',39),(16,'திரவ அளவீடு',59),(17,'திண்மப் பொருள்கள்',70),(18,'இயற்கணிதக் குறியீடுகள்',86),(19,'இயற்கணிதக் கோவைகளும் பிரதியீடும்',91),(20,'திணிவு',97),(21,'விகிதம்',109),(22,'தரவுகளைச் சேகரித்தலும் காட்டுதலும்',121),(23,'தரவுகளை விளக்குதல்',133),(24,'சுட்டிகள்',142),(25,'பரப்பளவு',148)]),
('science','Science','science G-6.pdf',12,[(1,'உயிர்ச்சூழலின் விந்தைகள்',1),(2,'எமது சூழலில் உள்ளவை',17),(3,'நீர் ஓர் இயற்கை வளம்',31),(4,'அன்றாட வாழ்வில் சக்தி',49),(5,'ஒளியும் பார்வையும்',66),(6,'ஒலியும் கேட்டலும்',84),(7,'காந்தம்',101),(8,'வசதியான வாழ்வுக்கு மின்சாரம்',116),(9,'வெப்பமும் அதன் விளைவுகளும்',134),(10,'போசணையை அடிப்படையாகக் கொண்ட இடைத்தொடர்பு',149),(11,'வானிலையும் காலநிலையும்',160)]),
('tamil','Tamil','Tamil lang & lit G6 (T).pdf',12,[(1,'தங்கம் விதைத்தால் தங்கம் விளையுமா?',1),(2,'எலியும் சேவலும்',15),(3,'ஏமாந்த நாய்',24),(4,'குரங்குச் சேட்டை',29),(5,'தந்தை மகனுக்கு எழுதிய கடிதம்',39),(6,'பறவைகள் பலவிதம்',48),(7,'அழ. வள்ளியப்பா பாடல்கள்',57),(8,'மூட ஆமை',62),(9,'தென்னமரக் கும்மி',67),(10,'புதிய ஆத்திசூடி',71),(11,'கண்ணகி வழக்குரைத்தல்',82),(12,'செய்தித்தாள்',90),(13,'செய்ந்நன்றி அறிதல்',95),(14,'சேர் ஐசாக் நியூற்றன்',100),(15,'புகழ்ச்சி இகழ்ச்சியை அளித்த கதை',103),(16,'ஈசலும் புற்றும்',106),(17,'ஒழுக்கம் உயர்வளிக்கும்',113),(18,'மரங்கள் வாழ்க! மாநிலம் வாழ்க!',116),(19,'குறும்பா',119),(20,'கம்பரிற் பாலர் கல்வி',122)]),
]

SECOND_SINHALA=[(1,'මේ මමයි',8),(2,'පාසල් යමු',14),(3,'තාත්තා එනවා',22),(4,'කුමාරිගෙන් ලියුමක්',28),(5,'හාවා නැටුම් පෑවා',34),(6,'නුවර මාමා එනවා',41),(7,'වැස්සක් එන්නේ',48),(8,'පූසා හැංගිලා',53),(9,'අපි පොළට යමු',59),(10,'යාපනේ මිදි',67),(11,'පිපාසිත කපුටා',76),(12,'දුම්රියෙන් ගමනක්',85)]

def convert(text):
    for old,new in MAPPING:text=text.replace(old,new)
    # Common residual Shree glyph sequences found in these editions.
    for old,new in [('ö\\','செ'),('÷\\','சே'),('\\','ச'),('ø\\','சை'),('ö\u00ad','செ')]:text=text.replace(old,new)
    return text
def clean(text):
    text=text.replace('\u00ad',' ');text=re.sub(r'[ \t]+',' ',text);text=re.sub(r'\n{3,}','\n\n',text)
    return text.strip()
def build(slug,subject,filename,offset,lessons):
    reader=PdfReader(BOOKS/filename);out=TARGET/'ta'/slug;out.mkdir(parents=True,exist_ok=True);items=[]
    for pos,(number,title,printed) in enumerate(lessons):
        first=printed+offset;last=(lessons[pos+1][2]+offset-1) if pos+1<len(lessons) else len(reader.pages);chunks=[]
        for page in range(first,last+1):
            text=clean(convert(reader.pages[page-1].extract_text() or ''))
            if text:chunks.append({'page':printed+(page-first),'index':0,'text':text})
        payload={'title':f'பாடம் {number}: {title}','subject':subject,'language':'ta','source':filename,'chunks':chunks}
        (out/f'lesson-{number}.json').write_text(json.dumps(payload,ensure_ascii=False),encoding='utf-8')
        items.append({'number':number,'title':title});print(subject,number,len(chunks))
    return items
def build_second_sinhala():
    filename='second la g-6 S.pdf';reader=PdfReader(BOOKS/filename);out=TARGET/'ta'/'second-sinhala';out.mkdir(parents=True,exist_ok=True);items=[]
    for pos,(number,title,printed) in enumerate(SECOND_SINHALA):
        first=printed+10;last=(SECOND_SINHALA[pos+1][2]+9) if pos+1<len(SECOND_SINHALA) else len(reader.pages);chunks=[]
        for page in range(first,last+1):
            text=clean(SINHALA_CONVERTER.convert(reader.pages[page-1].extract_text() or ''))
            if text:chunks.append({'page':printed+(page-first),'index':0,'text':text})
        payload={'title':f'දෙවන බස සිංහල {number}: {title}','subject':'Sinhala','language':'ta','source':filename,'chunks':chunks}
        (out/f'lesson-{number}.json').write_text(json.dumps(payload,ensure_ascii=False),encoding='utf-8');items.append({'number':number,'title':title});print('Second Language Sinhala',number,len(chunks))
    return items
def main():
    catalog=json.loads((TARGET/'catalog.json').read_text(encoding='utf-8'));catalog['ta']={}
    for slug,subject,filename,offset,lessons in SOURCES:
        book=catalog['ta'].setdefault(slug,{'subject':subject,'lessons':[]});book['lessons'].extend(build(slug,subject,filename,offset,lessons))
    catalog['ta']['second-sinhala']={'subject':'Sinhala','lessons':build_second_sinhala()}
    for book in catalog['ta'].values():book['lessons'].sort(key=lambda x:x['number'])
    (TARGET/'catalog.json').write_text(json.dumps(catalog,ensure_ascii=False,indent=2),encoding='utf-8')
    print('Tamil lessons',sum(len(x['lessons']) for x in catalog['ta'].values()))
if __name__=='__main__':main()

