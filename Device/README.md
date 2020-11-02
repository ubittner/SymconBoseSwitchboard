# Bose Switchboard Device  

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

* Gerät: Aus/An
* Stummschaltung: Aus/An
* Lautstärke: 0 - 100 %
* Voreinstellung: Presets 1 - 6
* Medieninformationen

### 2. Voraussetzungen

- IP-Symcon ab Version 5.5
- Bose Switchboard Splitter

### 3. Software-Installation

* Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Autor.
* Bei privater Nutzung wird das 'Bose Switchboard'-Modul über den Module Store installiert.  

### 4. Einrichten der Instanzen in IP-Symcon

Unter 'Instanz hinzufügen' kann das 'Bose Switchboard Device'-Modul mithilfe des Schnellfilters gefunden werden.  
Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen).

__Konfigurationsseite__:

Name                        | Beschreibung
--------------------------- | ------------------
Produkt ID                  | Produkt ID
Produkt Name                | Produkt Name
Produkt Typ                 | Produkt Typ
Aktualisierungsintervall    | Aktualisierungsintervall  
Merkmale:                   |
Audiobenachrichtigungen     | Merkmal Audiobenachrichtigung (nicht editierbar)
Stummschaltung              | Merkmal Stummschaltung (nicht editierbar)
Voreinstellungen            | Merkmal Voreinstellungen (nicht editierbar)
Volume Max                  | Merkmal Volume Max (nicht editierbar)
Volume Min                  | Merkmal Volume Min (nicht editierbar)


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

Name                        | Typ
--------------------------- | -------
BOSESB.InstanzID.Volume     | integer
BOSESB.InstanzID.Presets    | integer

### 6. WebFront

Die Funktionalität, die das Modul im WebFront bietet:  

* Gerät: Aus/An
* Stummschaltung: Aus/An
* Lautstärke: 0 - 100 %
* Voreinstellung: Presets 1 - 6
* Medieninformationen

### 7. PHP-Befehlsreferenz  

````
Aus- / Einschalten:  

bool BOSESB_ToggleDevicePower(integer $InstanzID, boolean $State);  
Schaltet das Gerät aus, bzw. an.  
Wurde der Befehl erfolgreich ausgeführt liefert er true zurück, andernfalls false.  

Werte für $State: false = aus, true = an

Beispiel:
BOSESB_ToggleDevicePower(12345, true);
````  

````
Stummschaltung:  

bool BOSESB_ToggleDeviceMute(integer $InstanzID, boolean $State);  
Schaltet das Gerät lautlos, bzw. wieder laut.  
Wurde der Befehl erfolgreich ausgeführt liefert er true zurück, andernfalls false.  
 
Werte für $State: false = laut, true = lautlos

Beispiel:
BOSESB_ToggleDeviceMute(12345, true);
````  

````
Lautstärke:  

bool BOSESB_SetDeviceVolume(integer $InstanzID, integer $Volume);  
Ändert die Lautstärke des Gerätes.  
Wurde der Befehl erfolgreich ausgeführt liefert er true zurück, andernfalls false.  

Werte für $Volume: 0 bis 100

Beispiel:
BOSESB_SetDeviceVolume(12345, 25);
````  

````
Preset 1 - 6:  

bool BOSESB_PlayDevicePreset(integer $InstanzID, integer $Preset);  
Spielt einen Preset von 1 bis 6 ab.  
Wurde der Befehl erfolgreich ausgeführt liefert er true zurück, andernfalls false.  

Werte für $Preset: 1 bis 6

Beispiel:
BOSESB_PlayDevicePreset(12345, 1);
````  

````
Aktualisierung:  

void BOSESB_UpdateDeviceState(integer $InstanzID);  
Aktualisiert die Geräteinformationen.  
Es erfolgt keine Rückmeldung  

Beispiel:
BOSESB_UpdateDeviceState(12345);
````  

````
Gerätemerkmale:  

bool BOSESB_GetDeviceCapabilities(integer $InstanzID);  
Liest die Gerätemerkmale aus.  
Wurde der Befehl erfolgreich ausgeführt liefert er true zurück, andernfalls false.  

Beispiel:
BOSESB_GetDeviceCapabilities(12345);
````  