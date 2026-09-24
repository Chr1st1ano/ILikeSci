import glob
import re

names = [
    'Maria Garcia', 'Noah Thomas', 'Emma Jackson', 'Lucas White', 'Ava Harris', 'Elijah Martin',
    'Linda Johnson', 'Oliver King', 'Evelyn Wright', 'Henry Scott', 'Harper Green', 'Alexander Baker',
    'Daniel Walker', 'Mia Hall', 'Henry Allen'
]

for f in sorted(glob.glob('*.html') + glob.glob('*.js') + glob.glob('*.php') + glob.glob('*.py')):
    with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
        content = fp.read()
        for n in names:
            if n in content:
                print(f"Match '{n}' in {f}")
