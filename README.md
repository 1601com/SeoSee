# SEO See
(Contao-Extension)

---

Einfaches laden und optimieren von JavaScript- und Style-Dateien

---

### Installation

```
$ composer require agentur1601com/seosee
```

---

### Einstellungen JS

* Die Quelle der JavaScript-Dateien wird über den sog. FileTree konfiguriert
* JavaScript-Dateien können einfach über das Seitenlayout (Contao-BE) per Checkbox 
  eingebunden werden
* Die Reihenfolge kann per Drag&Drop angepasst werden
* Das Laden der Dateien bietet folgende Optionen
  * Komprimieren
  * `async`
  * `defer`
  * `footer`
  * `preload`
  * `preload push`
* Die Reihenfolge kann per Drag&Drop angepasst werden

### Einstellungen CSS, SCSS, LESS

* Die einzelnen Style-Dateien könne über den Reiter (optimiertes CSS laden) geladen werden
* Die Reihenfolge kann per Drag&Drop angepasst werden
* Das Laden bietet folgende Möglichkeiten:
  * `Head`
  * `Footer`
  * `Preload`
  * `Preload push`
