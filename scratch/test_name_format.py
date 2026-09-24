import openpyxl
import re
import sys
import unicodedata

sys.stdout.reconfigure(encoding='utf-8')

def format_student_name(raw_name):
    if not raw_name:
        return ""
    name = unicodedata.normalize('NFC', str(raw_name))
    # Replace tabs/newlines
    name = re.sub(r'[\t\r\n]+', ' ', name)
    # Strip spaces
    name = name.strip()
    # Remove trailing ", -" or ",-"
    name = re.sub(r',\s*-\s*$', '', name)
    name = re.sub(r'-\s*$', '', name)
    # Ensure space after comma: "LAST,FIRST, MIDDLE" -> "LAST, FIRST, MIDDLE"
    name = re.sub(r',\s*', ', ', name)
    # Collapse multiple spaces
    name = re.sub(r'\s+', ' ', name)
    return name.strip()

# Let's test on sample names from both G4 and G6
test_names = [
    'AÑONUEVO, AXHER M.',
    'ALFORJA , ALWINA KHATE R.',
    'AMERIL, BORHAN,S',
    'ABASTILLAS,ALEX JADE, ABALLE',
    'CANARIA,BRIX GERALD,EGAÑA ',
    'ALTAMERA GIAN',
    'BUENA,SUMMER KYLE MATTHEW, -',
    '\tBAYNA, KIEFER EUVIN MOLINA',
    'AZUCENA,NEIL ZYRUS, -',
    'Anore, Sondai L.',
    'CASTALONE, KC.J',
    'MANITO, BRYAN BRANDO JR R'
]

for tn in test_names:
    print(f"'{tn}' -> '{format_student_name(tn)}'")
