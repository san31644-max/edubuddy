"""Build Grade 7 English textbook lesson caches from supplied PDFs."""
import json,re
from pathlib import Path
from pypdf import PdfReader
ROOT=Path(__file__).resolve().parents[1];BOOKS=ROOT/'uploads'/'syllabus'/'GRADE 7 ENGLISH MEDIUM';TARGET=ROOT/'uploads'/'syllabus'/'textbook-cache'/'grade-7'/'en'
SOURCES=[
('civic','Civic Education','Civic Edu G7 (E) PDF.pdf',10,[(1,'Our Family',1),(2,'Our Society',46),(3,'Our Culture',76)]),
('health','Health and Physical Education','health G-7 E.pdf',12,[(1,'Let us build a healthy family environment',1),(2,'Let us experience love and protection',13),(3,'Let us engage in folk games',28),(4,'Let us play volleyball',39),(5,'Let us play netball',49),(6,'Let us play football',56),(7,'Let us learn correct posture',64),(8,'Let us train for athletics',75),(9,'Let us develop healthy eating habits',82),(10,'Let us discover our body',103),(11,'Let us balance our emotions',121),(12,'Let us respect the rules, regulations and ethics in sports',131),(13,'Let us develop our physical fitness',140),(14,'Let us get ready for adolescence',156),(15,'Let us prevent non-communicable diseases',168)]),
('history','History','history G-7 E.pdf',8,[(1,'Lifestyle of Our Ancestors',1),(2,'Renowned Kings of Our Country',16),(3,'Our Cultural Heritage',36),(4,'Ruling Centres of Later Periods',53),(5,'Ancient Civilizations of the World',64)]),
('ict','ICT','ict G-7 E RB.pdf',10,[(1,'Central Processing Unit',1),(2,'Operating System',8),(3,'Security of Computer System',21),(4,'Word Processing',33),(5,'Programme Development',46),(6,'Presentation Software',69),(7,'Using Internet for Information and Communication',87)]),
('mathematics','Mathematics','maths G-7  P-I E.pdf',12,[(1,'Symmetry',1),(2,'Sets',13),(3,'Operations on Whole Numbers',20),(4,'Factors and Multiples',29),(5,'Indices',51),(6,'Time',57),(7,'Parallel Straight Lines',69),(8,'Directed Numbers',81),(9,'Angles',91),(10,'Fractions',113),(11,'Decimals',139),(12,'Algebraic Expressions',152)]),
('mathematics','Mathematics','maths G-7 P-II E.pdf',12,[(13,'Mass',1),(14,'Rectilinear Plane Figures',14),(15,'Equations and Formulae',26),(16,'Length',37),(17,'Area',52),(18,'Circles',64),(19,'Volume',72),(20,'Liquid Measurements',81),(21,'Ratios',91),(22,'Percentages',102),(23,'Cartesian Plane',109),(24,'Construction of Rectilinear Plane Figures',116),(25,'Solids',123),(26,'Data Representation and Interpretation',131),(27,'Scale Diagrams',141),(28,'Tessellation',148),(29,'Likelihood of an Event Occurring',154)]),
('science','Science','science G-7 P-I E.pdf',12,[(1,'Plant Diversity',1),(2,'Static Electricity',22),(3,'Generation of Electricity',34),(4,'Functions of Water',54),(5,'Acids and Bases',63),(6,'Animal Diversity',72),(7,'Forms of Energy and Uses',87),(8,'The Nature of the Earth',105),(9,'Light',115),(10,'The Correct Use of the Microscope',138)]),
('science','Science','science G-7 P-II E.pdf',12,[(11,'Sound',1),(12,'Biological Processes',11),(13,'Atmosphere',28),(14,'Heat and Temperature',40),(15,'Soil',59),(16,'Force and Motion',73),(17,'Nutrients in Food',86),(18,'Minerals and Rocks',100),(19,'Sources of Energy',113)]),
]
def clean(text):
 text=text.replace('\u00ad',' ');text=re.sub(r'[ \t]+',' ',text);text=re.sub(r'\n{3,}','\n\n',text);return text.strip()
def build(slug,subject,filename,offset,lessons):
 reader=PdfReader(BOOKS/filename);out=TARGET/slug;out.mkdir(parents=True,exist_ok=True);items=[]
 for pos,(number,title,printed) in enumerate(lessons):
  first=printed+offset;last=(lessons[pos+1][2]+offset-1) if pos+1<len(lessons) else len(reader.pages);chunks=[]
  for page in range(first,last+1):
   text=clean(reader.pages[page-1].extract_text() or '')
   if text:chunks.append({'page':printed+(page-first),'index':0,'text':text})
  (out/f'lesson-{number}.json').write_text(json.dumps({'title':f'Lesson {number}: {title}','subject':subject,'grade':7,'language':'en','source':filename,'chunks':chunks},ensure_ascii=False),encoding='utf-8')
  items.append({'number':number,'title':title});print(subject,number,len(chunks))
 return items
def main():
 catalog={}
 for slug,subject,filename,offset,lessons in SOURCES:catalog.setdefault(slug,{'subject':subject,'lessons':[]})['lessons'].extend(build(slug,subject,filename,offset,lessons))
 for book in catalog.values():book['lessons'].sort(key=lambda x:x['number'])
 (TARGET/'catalog.json').write_text(json.dumps(catalog,ensure_ascii=False,indent=2),encoding='utf-8');print('Grade 7 English lessons',sum(len(x['lessons']) for x in catalog.values()))
if __name__=='__main__':main()
