## Image to migration
20/01

- Detecteren van rechthoeken in PHP, Onmogelijk, zou wel lukken wel met python
- Python installeren en opencv (image processer)
- Detecteren van rechthoeken in python
    - http://stackoverflow.com/questions/14248571/finding-properties-of-sloppy-hand-drawn-rectangles

21/01

- Uitzoeken hoe coordinaten van rechthoeken te krijgen
- iets anders dan opencv? (conclusie neen, opencv is moeilijk (veel functies, moeilijke documentatie, verschillende versies, maar er is niks anders...))
- Tekst omzetten naar digitaal
    - Via python en openvc? -> Moet wel lukken
    - Machine learning? -> eerst leren welke contouren van cijfers zijn, dan classifiseren van letters
	    - Cijfers: http://stackoverflow.com/questions/9413216/simple-digit-recognition-ocr-in-opencv-python
    - Werkt wel met computer tekst, handgeschreven niet echt

22/01

- Zoeken API voor tekst herkenining
  Google Vision
- Tekst plaatsen in correcte kaders (wat is titel, wat is content...)
  Te weinig kennis van Python om daarin te doen --> python executen in php
- Toevoegen van datatypes

23/01

- Tekst wordt niet altijd goed gevonden, zoeken naar alternatief, opnieuw... Geen andere oplossing
- Maken van migrations van gevonden tabellen
- Leggen van relaties
  - Zoeken van kraaienvoeten
    - Template matching -> succes! (templateMulti)
    - Harris Corner
    - Hough Line
  - Zoeken van lijnen tussen tabellen
    - Hough Line -> niet gewenste resultaat, krijg niet de lijnen, moet wel mogelijk zijn om dikke lijnen te vinden