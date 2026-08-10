"""Split the supplied Grade 8/9 English textbooks into real chapter caches."""
import json,re
from pathlib import Path
from pypdf import PdfReader
ROOT=Path(__file__).resolve().parents[1];BASE=ROOT/'uploads'/'syllabus';TARGET=BASE/'textbook-cache'

G8='grade 8 english medium';G9='GRADE 9 ENGLISH MEDIUM'
SOURCES={
8:[
('ict','ICT','book.pdf',10,[(1,'Number Systems',1),(2,'Configuring and Formatting a Computer',11),(3,'Word Processing',34),(4,'Programming',39),(5,'Physical Computing',55),(6,'Internet',63)]),
('civic','Civic Education','Civic Education Gr 8 (E)_V1.pdf',10,[(1,'Public Services',1),(2,'Democratic Society',32),(3,'Multicultural Society',51),(4,'Contemporary Issues',69),(5,"Let's Overcome Challenges by Solving Problems",104),(6,'Let Us Enter the World of Work',117)]),
('english','English','en PB g-8.pdf',9,[(1,'Plan the Work, Work the Plan',1),(2,'Winged Friends',12),(3,"Let's Be Considerate",23),(4,'Mother Nature',34),(5,'Between the Miles',49),(6,'When We Are Together',62),(7,'The World of Children',71),(8,"It's a Small World",86),(9,'On Top of the World',97),(10,'Beyond the Classroom',104)]),
('geography','Geography','geo G-8 E.pdf',10,[(1,'The Solar System',1),(2,'Uniqueness of the Earth as a Habitat of Living Beings',17),(3,'Basic Features of 1:50,000 Topographic Maps of Sri Lanka',29),(4,'South Asia',49)]),
('history','History','history G-8 E.pdf',10,[(1,'Traditional Technologies and Arts of Sri Lanka',1),(2,'Kandyan Kingdom',35),(3,'The Renaissance in Europe',51),(4,'Explorations and Arrival of the Europeans in the East',63),(5,'Capture of Coastal Areas by the Portuguese',75)]),
('mathematics','Mathematics','maths G-8 P-I E.pdf',11,[(1,'Number Patterns',1),(2,'Perimeter',14),(3,'Angles',22),(4,'Directed Numbers',37),(5,'Algebraic Expressions',49),(6,'Solids',67),(7,'Factors',80),(8,'Square Root',90),(9,'Mass',100),(10,'Indices',112),(11,'Symmetry',121),(12,'Triangles and Quadrilaterals',128),(13,'Fractions Part I',144),(14,'Fractions Part II',156)]),
('mathematics','Mathematics','maths G-8 P-II E.pdf',11,[(15,'Decimal Numbers',1),(16,'Ratios',13),(17,'Equations',24),(18,'Percentages',32),(19,'Sets',41),(20,'Area',47),(21,'Time',61),(22,'Volume and Capacity',77),(23,'Circles',86),(24,'Location of a Place',93),(25,'Number Line and Cartesian Plane',100),(26,'Construction of Triangles',114),(27,'Data Representation and Interpretation',122),(28,'Scale Diagrams',140),(29,'Probability',149),(30,'Tessellation',159)]),
('science','Science','science G8 P-I E.pdf',10,[(1,'Importance of Microorganisms',1),(2,'Animal Classification',12),(3,'Diversity and Functions of Plant Parts',24),(4,'Properties of Matter',39),(5,'Sound',62),(6,'Magnets',78),(7,'Measurements Associated with Electricity',95),(8,'Changes in Matter',107)]),
('science','Science','science G-8 P-II E.pdf',10,[(9,'Human Organ Systems',1),(10,'Electricity',18),(11,'Main Biological Processes in Plants',46),(12,'Life Cycles of Living Organisms',62),(13,'Food Preservation',80),(14,'Phenomena and Exploration Associated with the Solar System',97),(15,'Natural Disasters',128)])],
9:[
('civic','Civic Education','Unconfirmed 241062.crdownload',10,[(1,'Social Security',1),(2,'Contemporary Changes',29),(3,'Democratic Governance',72),(4,'Local Government Institutions',96),(5,'Conflict Resolution',117),(6,'World of Work',127)]),
('english','English','Unconfirmed 492737.crdownload',9,[(1,'Everybody Is Good at Something',1),(2,'May I Help You?',9),(3,'Meeting',19),(4,'Extinct Friends',29),(5,'A Second Chance Called Tomorrow',44),(6,'Art',60),(7,'Where We Are',72),(8,'Success Through Creativity',84),(9,'The Greatest Wealth',91),(10,'Be Happy, Be Bright, Be You!',107)]),
('geography','Geography','geo G-9 E.pdf',9,[(1,'The Asian Region',1),(2,'Landscape of Sri Lanka',23),(3,'Spatial Changes of Development in Sri Lanka',67),(4,'Environmental Balance',75),(5,'Reading 1:50,000 Topographic Maps of Sri Lanka',83)]),
('health','Health and Physical Education','health G9 E.pdf',0,[(1,'Let Us Build a Healthy Society',1),(2,'Let Us Achieve Self Actualization',14),(3,'Let Us Identify Physical Deformities',20),(4,'Let Us Identify Organized Games and Outdoor Education',29),(5,'Let Us Play Volleyball',37),(6,'Let Us Play Netball',47),(7,'Let Us Play Football',55),(8,'Let Us Train for Relay Races',65),(9,'Let Us Practise Long Jump',73),(10,'Let Us Fulfil Our Nutritional Needs',79),(11,'Let Us Protect Our Body Features',100),(12,'Let Us Develop Health-related Fitness',116),(13,'Let Us Be Familiar with Knots',125),(14,'Let Us Enjoy Making Bonfires',133),(15,'Let Us Cook Food Outdoors',141),(16,'Let Us Train for High Jump',150),(17,'Let Us Practise Throwing',156),(18,'Let Us Develop Social Values',165),(19,'Let Us Improve Psychosocial Skills',173),(20,'Let Us Identify Gender Responsibilities',182),(21,'Let Us Overcome Social Health Problems',191)]),
('ict','ICT','ICT reading G-9 E.pdf',10,[(1,'Preparation of Computer Specifications',1),(2,'Electronic Spreadsheets',22),(3,'Programming',29),(4,'Use of Microcontrollers',47),(5,'Computer Networks',66),(6,'ICT and Society',75)]),
('mathematics','Mathematics','maths G-9 P- IE.pdf',9,[(1,'Number Patterns',1),(2,'Binary Numbers',16),(3,'Fractions',27),(4,'Percentages',42),(5,'Algebraic Expressions',60),(6,'Factors of Algebraic Expressions',69),(7,'Axioms',81),(8,'Angles Related to Straight and Parallel Lines',94),(9,'Liquid Measurements',117)]),
('mathematics','Mathematics','maths G-9 P-III E.pdf',9,[(21,'Inequalities',1),(22,'Sets',10),(23,'Area',28),(24,'Probability',42),(25,'Angles of Polygons',51),(26,'Algebraic Fractions',65),(27,'Scale Diagrams',79),(28,'Data Representation and Interpretation',93)]),
('science','Science','Unconfirmed 859446.crdownload',12,[(1,'Applications of Micro-organisms',1),(2,'Eye and Ear',16),(3,'Nature and Properties of Matter',38),(4,'Basic Concepts Associated with Force',52),(5,'Pressure Exerted by Solids',60),(6,'Human Circulatory System',72),(7,'Plant Growth Substances',83),(8,'Support and Movements of Organisms',89),(9,'The Evolutionary Process',98)]),
('science','Science','Science Part II English G-9.pdf',12,[(10,'Electrolysis',1),(11,'Density',10),(12,'Biodiversity',19),(13,'Artificial Environment and Green Concept',42),(14,'Reflection and Refraction of Waves',58),(15,'Simple Machines',84),(16,'Nanotechnology and Its Applications',103),(17,'Lightning Accidents',119),(18,'Natural Disasters',129),(19,'Sustainable Use of Natural Resources',152)])]}




def clean(text):
 text=text.replace('\u00ad',' ');text=re.sub(r'[ \t]+',' ',text);return re.sub(r'\n{3,}','\n\n',text).strip()
for grade,sources in SOURCES.items():
 folder=BASE/(G8 if grade==8 else G9);catalog={}
 for slug,subject,filename,offset,lessons in sources:
  reader=PdfReader(folder/filename);out=TARGET/f'grade-{grade}'/'en'/slug;out.mkdir(parents=True,exist_ok=True)
  for pos,(number,title,printed) in enumerate(lessons):
   first=printed+offset;last=(lessons[pos+1][2]+offset-1) if pos+1<len(lessons) else len(reader.pages);chunks=[]
   for page in range(first,min(last,len(reader.pages))+1):
    text=clean(reader.pages[page-1].extract_text() or '')
    if text:chunks.append({'page':printed+(page-first),'index':0,'text':text})
   (out/f'lesson-{number}.json').write_text(json.dumps({'title':title,'subject':subject,'grade':grade,'language':'en','source':filename,'chunks':chunks},ensure_ascii=False),encoding='utf-8')
   catalog.setdefault(slug,{'subject':subject,'lessons':[]})['lessons'].append({'number':number,'title':title});print(grade,subject,number,title,len(chunks))
 for book in catalog.values():book['lessons'].sort(key=lambda x:x['number'])
 (TARGET/f'grade-{grade}'/'en'/'catalog.json').write_text(json.dumps(catalog,ensure_ascii=False,indent=2),encoding='utf-8')
