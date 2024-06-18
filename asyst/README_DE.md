<h2>Inhalt:</h2>
<ul>  
   <li><a href="#1"> Was ist ASYST? </a> </li>
   <li><a href="#2"> Welche Sprachen unterstützt ASYST? </a></li> 
   <li><a href="#3"> Wie verwendet man ASYST? </a></li> 
   <ol>
      <li><a href="#4"> Wie müssen auszuwertende Daten formatiert sein?</a> </li>
      <li><a href="#5"> Wie führe ich das Programm unter Windows 11 aus? </a> </li>

   </ol>
   <li><a href="#6"> Wie arbeit man mit der Ausgabe von ASYST weiter? </a></li> 
   <li><a href="#7"> Wie kann ich ASYST ausführen, wenn ich kein Windows 11 nutze? </a> 
         <ul><li><a href="#8"> Ausführen von ASYST in der Entwicklungsumgebung Pycharm</a> </li></ul></li>
</ul>


<h2 id=1>Was ist ASYST?</h2>

ASYST ist ein Programm, das Lehrenden die Auswertung von Freitextantworten in Tests erleichtern soll: Mit Hilfe
künstlicher Intelligenz
macht ASYST Bewertungsvorschläge, die von den Lehrenden gezielt überprüft und ggf. verändert werden können.

ASYST ist für die Bewertung von Freitext-Kurzantworten gedacht - diese Kurzantworten sollten etwa ein bis drei Sätze
umfassen. Für längere Antworten ist die Anwendung nicht vorgesehen.

ASYST hilft der Lehrperson, indem es eine Bewertung vorschlägt. Diese Bewertung kann im Einzelfall durchaus auch falsch
sein; die Lehrperson kann sie prüfen und korrigieren.
Dabei spart man gegenüber der völlig manuellen Bewertung an zwei Stellen Zeit: Zum Einen ist das Überprüfen von
Bewertungen im Allgemeinen schneller als das Bewerten von Grund auf;
und zum anderen empfehlen wir, bei der Überprüfung gezielt die ASYST-Bewertungen auszuwählen, die eher fehleranfällig
sind (s. Abschnitt <a href="#6"> Wie arbeit man mit der Ausgabe von ASYST weiter? </a>).

Das Programm ist in Python geschrieben; der Quellcode ist öffentlich zugänglich. Um ASYST einfacher nutzbar zu machen,
wurden die Python-Skripte
in eine ausführbare Programmdatei umgewandelt, die in Windows 11 nutzbar ist.

Die technischen Hintergründe zu ASYST und eine Beispielrechnung zum Einsatz für das Deutsche finden sich
in <a href="https://rdcu.be/dxPLg">Pado, Eryilmaz und Kirschner, IJAIED 2023</a>.

<h2 id=2>Welche Sprachen unterstützt ASYST?</h2>

ASYST wurde für <a href="https://rdcu.be/dxPLg">Deutsch</a> und  <a href="https://nlpado.de/~ulrike/papers/Pado22.pdf">
Englisch</a> getestet.

Das Sprachmodell, das Deutsch abdeckt, kann im Prinzip noch weitere Sprachen verarbeiten. Sie können also
grundsätzlich "Deutsch" als Spracheinstellung auswählen und Daten in einer der anderen unterstützten Sprachen hochladen.
Bitte prüfen Sie die Ergebnisse aber sorgfältig, es liegen keine Erfahrungen vor! (Die Sprachen
sind <a href="https://www.sbert.net/docs/pretrained_models.html#multi-lingual-models">lt. den Modellerstellern</a>: ar,
bg, ca, cs, da, de, el, en, es, et, fa, fi, fr, fr-ca, gl, gu, he, hi, hr, hu, hy, id, it, ja, ka, ko, ku, lt, lv, mk,
mn, mr, ms, my, nb, nl, pl, pt, pt-br, ro, ru, sk, sl, sq, sr, sv, th, tr, uk, ur, vi, zh-cn, zh-tw.)

<h2 id=3>Wie verwendet man ASYST?</h2>
Wir haben bei der Entwicklung von ASYST versucht, die Verwendung möglichst einfach zu machen sein.

<h3 id=4>Wie müssen auszuwertende Daten formatiert sein?</h3>
Das Programm arbeitet auf Basis Ihrer Daten im Excel-Format .xlsx (das auch von Libre Office Calc und anderen Programmen
erzeugt werden kann). Eine Beispieltabelle:

![table_input.png](images%2Ftable_input.png)

Dabei müssen die folgende Informationen in der **richtigen Reihenfolge** und mitem **richtigen Titel** der Spalten
enthalten sein:

1) **Question**: Die gestellte Frage
2) **referenceAnswer**: Eine korrekte Antwort / Musterlösung / Referenzantwort
3) **studentAnswer**: Die vom Prüfling gegebene Antwort, die bewertet werden soll.
5) (optional) **observed grade**: Hier kann die tatsächliche Bewertung durch die Lehrkraft eingetragen werden, um
   Kennzahlen über die Richtigkeit der Vorhersagen zu bekommen.

Die Beispieltabelle finden Sie
unter <a href="https://transfer.hft-stuttgart.de/gitlab/ulrike.pado/ASYST/-/blob/main/DE_Demo_Daten.xlsx">
DE_Demo_Daten.xlsx</a>. Sie enthält einige Fragen und Antworten aus dem CSSAG-Korpus (Computer Science Short Answers in
German) der HFT Stuttgart. Das Korpus is CC-BY-NC lizenziert.

<h3 id=5>Wie führe ich das Programm unter Windows 11 aus? </h3>

Zunächst muss die Datei
_ASYST.exe_ <a href="https://transfer.hft-stuttgart.de/gitlab/ulrike.pado/ASYST/-/blob/main/ASYST.exe"> heruntergeladen
werden</a>.
Sobald dies geschehen ist, kann das Programm mittels Doppelklick gestartet werden.

Der Start des Programmes wird eine Weile dauern (ca 1 Minute). In dieser Zeit wird das System initialisiert.

**Hinweis**: Es kann passieren, dass Windows Defender davor warnt, die Anwendung auszuführen, da das Programm kein
Sicherheitszertifikat besitzt.
Durch Auswählen von _weitere Informationen_ und anschließend _Trotzdem ausführen_ verschwindet die Fehlermeldung und
ASYST kann ausgeführt werden. Der Quelltext von ASYST ist offen zugänglich, so dass Sie sich vergewissern können, dass
ASYST keine Schadsoftware ist.


<img src="images/win_def_de_1.JPG" width="450">
<img src="images/win_def_de_2.JPG" width="450">

Nachdem das Programm gestartet wurde, erscheint eine Oberfläche, auf der die Sprache der auszuwertenden Antworten
ausgewählt werden kann.
Anschließend kann über einen Klick auf das Feld "Input File" die zu verarbeitende Tabelle ausgewählt werden.
Hierbei sollten die Daten wie oben beschrieben angeordnet sein.
Nach einem Klick auf das "Start"-Feld beginnt ASYST mit der Verarbeitung der Daten. Dies kann wiederum eine Weile
dauern (1-2 Minuten, relativ unabhängig von der Menge der zu verarbeitenden Daten).

Sobald das Programm alle Einträge verarbeitet und Vorhersagen getroffen hat, öffnet sich eine Tabellenansicht mit der
Überschrift "Results" (Ergebnisse).

Die Ergebnistabelle enthält alle Spalten der eingelesenen Tabelle, sowie zusätzlich in der Spalte "predicted grade" die
von ASYST vorgeschlagene Bewertung der Antworten. Die "incorrect"-Einträge der als falsch eingestuften Antworten sind
rot hinterlegt. Sie können in dieser Tabelle allerdings noch keine Bewertungen verändern. Speichern Sie hierzu über
einen Klick auf "Save as" die erzeugte Tabelle und öffnen Sie sie dann mit einem Tabellenkalkulationsprogramm.

![table_results.png](images%2Ftable_results.png)

Sobald die Ergebnistabelle angezeigt wird, kann ASYST die nächste Tabelle einlesen und verarbeiten.

**ACHTUNG: Die Ergebnistabelle wird nicht automatisch gespeichert.** Werden die Ergebnisse nicht gespeichert,
wird die Erbgebnistabelle im nächsten Durchlauf überschrieben.
Daher sollte, um die Ergebnisse zu sichern, auf den **"Save as"**- Button geklickt und die Ausgabetabelle am gewünschten
Ort gespeichert werden.

<h2 id=6>Wie arbeitet man mit der Ausgabe von ASYST weiter?</h2>

Wir empfehlen die folgende **Vorgehensweise** beim Einsatz von ASYST:

(Weitere Informationen und eine konkretes Beispiel für das Vorgehen liefert der
Artikel <a href="https://nlpado.de/~ulrike/papers/Pado22.pdf">_Assessing the Practical Benefit of Automated Short-Answer
Graders_</a>.)

1) **Definition der Anforderungen**: Wie genau muss die Bewertung in meinem aktuellen Anwendungsfall sein?
   <ul>
   <li>Bei der Bewertung von Freitextfragen in eher informellen Testsituationen (keine Abschlussklausur o.ä.) unterscheiden sich auch <b>menschliche Bewertungen</b> in ca. 15% der Fälle - 0% Abweichung sind also auch für Menschen kaum erreichbar! </li>
   <li>Wir empfehlen daher in solchen Situationen, eine Bewertungsgenauigkeit von mindestens 85% auch nach dem Einsatz von ASYST plus der menschlichen Korrektur anzustreben. </li>
   <li>Zu Beachten ist zudem die Verteilung der Bewertungsfehler (Übermäßige Strenge/Milde)</li>
   <li>Letztlich sollte die Verwendung des Tools den Anwender:innen eine Zeitersparnis bringen: Setzen Sie das verfügbare Budget oder eine angestrebte Mindestersparnis fest. </li>
   </ul>

2) **Sammeln von** manuell bewerteten **Testdaten:**

   Um einen Eindruck von der Genauigkeit und Zuverlässigkeit des automatischen Bewerters zu bekommen, werden annotierte
   Testdaten benötigt,
   d.h. Eingabe-Daten, für die eine korrekte Klassifizierung bereits festgelegt ist. Es werden also Daten im einlesbaren
   Format benötigt, die bereits manuell bewertet wurden. Dies können z.B. Antworten aus früheren Tests sein.
   Um den Datensatz möglichst robust gegenüber zufälligen Schwankungen zu machen, sollte er idealerweise einige hundert
   Antworten umfassen -- aber kleinere Datensätze können natürlich ebenfalls verwendet werden.

4) **Analyse** der Leistung der automatischen Bewertung

   Anhand der manuell bewerteten Testdaten kann nun gemessen werden, wie zuverlässig und treffsicher der Klassifizierer
   für die spezifischen Fragen arbeitet. Damit bekommen Sie einen Eindruck davon, wie gut die Vorhersage für Ihren
   eigenen Datensatz funktioniert.

   Hierzu werden die Fragen und Antworten aus dem Testdatensatz von ASYST verarbeitet und anschließend die erhaltene
   Klassifikation mit der manuellen Bewertung abgeglichen (z.B. in einer Tabellenkalkulation wie Excel oder Libre Office
   Calc).

   Dabei kann der Anteil der korrekt klassifizierten Antworten im gesamten Datensatz ermittelt werden - dieser sollte
   85% oder höher betragen (das entspricht einer Fehlerquote von 15% oder weniger).

   Sie können auch für die einzelnen Bewertungen (richtig/falsch) berechnen, wie groß die Präzision für die
   verschiedenen Bewertungen jeweils ist. Die Präzision misst, wie viele Vorhersagen einer bestimmten Bewertung
   tatsächlich richtig waren, d.h. wie vertrauenswürdig die Vorhersagen des Bewerters für ein bestimmtes Label sind. So
   bedeutet eine Präzision von 75% für die Bewertung "korrekt", dass drei Viertel aller Vorhersagen von "korrekt"
   gestimmt haben, aber in einem Viertel der Fälle die Antwort laut der manuellen Bewertung falsch war.

   _(Die Funktion, diese Kenngrößen der Zuverlässigkeit automatisch in einem Testmodus zu generieren soll in Zukunft dem
   Programm noch hinzugefügt werden.)_

5) **Entscheidung** wie der Ansatz genutzt werden soll.

   Anhand der erhobenen Kenngrößen zur Zuverlässigkeit für die oben genannten Kriterien kann nun eine Entscheidung
   getroffen werden.
   <ul>
   <li> Wie groß ist der Anteil der korrekt vorhergesagten Bewertungen? Beträgt er >85%, können Sie die ASYST-Vorhersagen sogar unverändert übernehmen, falls Sie dies wünschen. </li>
   <li> Wie ist die Präzision der einzelnen Bewertungsklassen (richtig/falsch)? Wenn eine der Klassen deutlich zuverlässiger vorhergesagt wird, können Sie entscheiden, diese Vorhersagen ungeprüft zu übernehmen und <b>nur</b> die Vorhersagen für die weniger verlässlich erkannte Klasse zu überprüfen. Dies führt in der Praxis zu einer deutlichen Zeitersparnis. </li>
   <li>Wie ist der Bewertungsfehler verteilt? Werden übermäßig viele korrekte Antworten als falsch bewertet, oder umgekehrt? Ist dies für Ihre Situation akzeptabel? </li>
   <li> Wie viel Bewertungsaufwand hätten Sie für den Beispieldatensatz eingespart, z.B. indem Sie die verlässlichere Bewertungsklasse ungeprüft akzeptieren?
   </ul>

<h3 id=7>Wie kann ich ASYST ausführen, wenn ich kein Windows 11 nutze?</h3>
Die klickbare Anwendung "ASYST.exe" eignet sich nur für die Ausführung unter Windows 11.

In anderen Betriebssystemen kann ASYST aus einer Entwicklungsumgebung heraus ausgeführt werden.
Der ASYST-Quellcode ist ursprünglich in Python geschrieben und kann daher robust in verschiedenen Umgebungen ausgeführt
werden.
Für Anwender, die mit dem Ausführen von Python-Programmen nicht vertraut sind, wird im folgenden eine Möglichkeit näher
beschrieben.
<h4 id=8>Ausführen von ASYST in der Entwicklungsumgebung Pycharm </h4>
<ol>
<li>Falls noch nicht geschehen, die Entwicklungsumgebung Pycharm aus dem Internet 
    <a href="https://www.jetbrains.com/pycharm/download/?section=mac"> herunterladen </a> und installieren. 
    Für mehr Informationen und Problemlösung siehe 
    <a href="https://www.jetbrains.com/help/pycharm/installation-guide.html"> Pycharm-Installationsguide</a>.</li>
<li>Python installieren 

Die Entwicklung von ASYST erfolgte in Python 3.10 - daher wird diese Version für die Ausführung empfohlen.
Die zum Betriebssystem passende Version kann unter https://www.python.org/downloads ausgewählt und installiert werden.
</li>

<li> Den Quellcode aus Gitlab in die Entwicklungsumgebung herunterladen:

Get from VCS


<img src="images/get_from_vcs.png" width="450">


im Feld _url_ folgenden Pfad eintragen: git@transfer.hft-stuttgart.de:ulrike.pado/ASYST.git


<img src="images/svn_url.png" width="450">


Anschließend auf _clone_ klicken und warten
</li>

<li>Entwicklungsumgebung konfigurieren
**Python-Interpreter konfigurieren:**

Navigiere zu   _Settings >> Project ASYST >> Python Interpreter >> Add Interpreter >> Add local Interpreter_

![add_interpreter.png](images%2Fadd_interpreter.png)

![create_venv.png](images%2Fcreate_venv.png)

_Location_: [Projektpfad]/[Projektname]/Source,

_Base interpreter_:  Pfad zur installierten Pythonversion

*Benötigte Pakte installieren:*
Falls Pycharm nicht von sich aus vorschlägt, die in der requirements.txt aufgeführten Pakete zu installieren,
führe manuell über das Terminal von PyCharm folgende Befehle aus:

'''
> cd Source
>
>
> pip install -r requirements.txt

'''

</li>
<li>ASYST ausführen

![run_button.png](images%2Frun_button.png)

Nachdem über das Projektverzeichnis links die Datei _main.py_ ausgewählt wurde, wird der ausgegraute _Startknopf_ oben
rechts
im Fenster grün. Ein einfacher Klick genügt, und ASYST wird ausgeführt.

</li>
</ol>

