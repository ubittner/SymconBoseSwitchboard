# Bose Switchboard Device (beta)  

![Image](../imgs/bose_logo_blackbox_80x80.png)  

Steuert ein Bose Switchboard Gerät.  

Für dieses Modul besteht kein Anspruch auf Fehlerfreiheit, Weiterentwicklung, sonstige Unterstützung oder Support.  
Bevor das Modul installiert wird, sollte unbedingt ein Backup von IP-Symcon durchgeführt werden.  
Der Entwickler haftet nicht für eventuell auftretende Datenverluste oder sonstige Schäden.  
Der Nutzer stimmt den o.a. Bedingungen, sowie den Lizenzbedingungen ausdrücklich zu.  

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Power: Aus / An
* Mute: Aus / An
* Volume
* Presets
* Now Playing

### 2. Voraussetzungen

- IP-Symcon ab Version 5.4
- Bose Switchboard Splitter

### 3. Software-Installation

* Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Autor.
* Bei privater Nutzung wird das 'Bose Switchboard'-Modul über den Module Store installiert.
* Alternativ über das Module Control folgende URL hinzufügen: `https://github.com/ubittner/SymconBoseSwitchboard.git`  

### 4. Einrichten der Instanzen in IP-Symcon

Unter 'Instanz hinzufügen' kann das 'Bose Switchboard Device'-Modul mithilfe des Schnellfilters gefunden werden.  
Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name                    | Beschreibung
----------------------- | ------------------
Notiz                   | Notiz
Produkt ID              | Produkt ID
Produkt Name            | Produkt Name
Produkt Typ             | Produkt Typ
Audiobenachrichtigungen | Merkmal Audiobenachrichtigung (nicht editierbar)
Stummschaltung          | Merkmal Stummschaltung (nicht editierbar)
Voreinstellungen        | Merkmal Voreinstellungen (nicht editierbar)
Volume Max              | Merkmal Volume Max (nicht editierbar)
Volume Min              | Merkmal Volume Min (nicht editierbar)
Aktualisierung          | Aktualisierung  

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name        | Typ       | Beschreibung
----------- | --------- | ------------
Power       | boolean   | Power
Mute        | boolean   | Mute
Volume      | integer   | Volume
Presets     | integer   | Presets
Now Playing | string    | Now Playing  

#### Profile

Name                    | Typ
----------------------- | -------
BSBD.InstanzID.Volume   | integer
BSBD.InstanzID.Presets  | integer

### 6. WebFront

Die Funktionalität, die das Modul im WebFront bietet:
* Power: Aus / An
* Mute: Aus / An
* Volume
* Presets
* Now Playing

### 7. PHP-Befehlsreferenz  

````
BSBD_TogglePower(integer $InstanzID, boolean $State);  
Schaltet das Gerät aus, bzw. an.      

Werte für $State: false = aus, true = an

Beispiel:
BSBD_TogglePower(12345, true);
````  

````
BSBD_ToggleMute(integer $InstanzID, boolean $State);  
Schaltet das Gerät lautlos, bzw. wieder laut.      

Werte für $State: false = laut, true = lautlos

Beispiel:
BSBD_ToggleMute(12345, true);
````  

````
BSBD_ChangeVolume(integer $InstanzID, integer $Volume);  
Ändert die Lautstärke des Gerätes.      

Werte für $Volume: 0 bis 100

Beispiel:
BSBD_ChangeVolume(12345, 25);
````  

````
BSBD_PlayPreset(integer $InstanzID, integer $Preset);  
Spielt einen Preset von 1 bis 6 ab.    

Werte für $Preset: 1 bis 6

Beispiel:
BSBD_PlayPreset(12345, 1);
````  

````
BSBD_UpdateState(integer $InstanzID);  
Aktualisiert die Geräteinformationen.    

Beispiel:
BSBD_UpdateState(12345);
````  