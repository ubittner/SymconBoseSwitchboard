# Bose Switchboard Splitter (beta)  

![Image](../imgs/bose_logo_blackbox_80x80.png)  

Stellt eine Verbindung mit der Bose Switchboard Cloud API her.  

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

* Bose Switchboard Cloud API (0.9)

### 2. Voraussetzungen

- IP-Symcon ab Version 5.4
- Gültige IP-Symcon Subskription
- Bose Home Speaker 300
- Bose Home Speaker 450
- Bose Home Speaker 500
- Bose Portable Home Speaker
- Bose Soundbar 500
- Bose Soundbar 700

### 3. Software-Installation

* Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Autor.
* Bei privater Nutzung wird das 'Bose Switchboard'-Modul über den Module Store installiert.
* Alternativ über das Module Control folgende URL hinzufügen: `https://github.com/ubittner/SymconBoseSwitchboard.git`  

### 4. Einrichten der Instanzen in IP-Symcon

Unter 'Instanz hinzufügen' kann das 'Bose Switchboard Splitter'-Modul mithilfe des Schnellfilters gefunden werden.  
Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ------------------
Notiz    | Notiz
Timeout  | Timeout
Token    | Token

### 5. Statusvariablen und Profile
 
Es werden keine Statusvariablen und Profile verwendet.

### 6. WebFront

Die Splitter Instanz ist im WebFront nicht verfügbar. 

### 7. PHP-Befehlsreferenz

Für einzelne Befehle wird die Produkt-ID des Bose Produkts benötigt.  
Die Produkt-ID ist eine eindeutige GUID (Globally unique identifier) für ein Bose Produkt und  
wird in der Instanz-Konfiguration des Bose Gerätes aufgeführt.  

Beispiel: 4aa8fe15-d16c-23ba-e42b-86dc75a3ed09

````
BSBS_ListProducts(integer $InstanzID);
Listet die für den aktuellen Benutzer verfügbaren Bose Produkte auf.  

Beispiel:
BSBS_ListProducts(12345);
````  

````
BSBS_GetProduct(integer $InstanzID, string $ProduktID);
Ruft Informationen über ein einzelnes Bose Produkt ab.  
Nur Produkte, die von ListProducts zurückgegeben werden, sind für den angegebenen Benutzer zugänglich.  

Beispiel:
BSBS_GetProduct(12345, '4aa8fe15-d16c-23ba-e42b-86dc75a3ed09');
````  

````
BSBS_ChangeMuteSetting(integer $InstanzID, string $ProduktID, string $Stummschaltung);  
Sendet eine Aufforderung zur Änderung der Einstellung für die Stummschaltung des Lautsprechers.  

Werte für $Stummschaltung: "ON" "OFF" "TOGGLE"  

Beispiel:
BSBS_ChangeMuteSetting(12345, '4aa8fe15-d16c-23ba-e42b-86dc75a3ed09', 'ON');
````  

````
BSBS_ChangePowerSetting(integer $InstanzID, string $ProduktID, string $Power);  
Sendet eine Aufforderung zum Ein- / Ausschalten des Lautsprechers.  

Werte für $Power: "ON" "STANDBY" "TOGGLE"

Beispiel:
BSBS_ChangePowerSetting(12345, '4aa8fe15-d16c-23ba-e42b-86dc75a3ed09', 'ON');
````  

````
BSBS_ChangeVolume(integer $InstanzID, string $ProduktID, integer $Lautstärke);  
Sendet eine Aufforderung zur Änderung der Lautstärke des Lautsprechers.  

Werte für $Lautstärke: 0 bis 100

Beispiel:
BSBS_ChangeVolume(12345, '4aa8fe15-d16c-23ba-e42b-86dc75a3ed09', 15);
````  

````
BSBS_GetNowPlaying(integer $InstanzID, string $ProduktID);  
Ruft die aktuellsten Informationen der Wiedergabe ab, die für dieses Produkt verfügbar sind.  

Beispiel:
BSBS_GetNowPlaying(12345, '4aa8fe15-d16c-23ba-e42b-86dc75a3ed09');
````  

````
BSBS_ControlProduct(integer $InstanzID, string $ProduktID, string $Befehl);  
Ändert den Wiedergabestatus eines Produkts.   

Werte für $Befehl: "RESUME" "PAUSE" "RESUME_PAUSE_TOGGLE" "SKIP_NEXT" "SKIP_PREVIOUS"  

Beispiel:
BSBS_ControlProduct(12345, '4aa8fe15-d16c-23ba-e42b-86dc75a3ed09', 'PAUSE');
````  

````
BSBS_SetRepeat(integer $InstanzID, string $ProduktID, string $Wiederholung);  
Setzt die aktuelle Inhaltsquelle auf Wiederholung (falls verfügbar).   

Werte für $Wiederholung: "ONE" "ALL" "OFF"  

Beispiel:
BSBS_SetRepeat(12345, '4aa8fe15-d16c-23ba-e42b-86dc75a3ed09', 'ONE');
````  

````
BSBS_SetShuffle(integer $InstanzID, string $ProduktID, string $Zufallswiedergabe);  
Setzt die aktuelle Inhaltsquelle auf Zufallswiedergabe (falls verfügbar).     

Werte für $Zufallswiedergabe: "ON" "OFF" "TOGGLE"  

Beispiel:
BSBS_SetShuffle(12345, '4aa8fe15-d16c-23ba-e42b-86dc75a3ed09', 'ON');
````  

````
BSBS_ListPresets(integer $InstanzID, string $ProduktID);  
Listet die Voreinstellungen auf, die dem aktuellen Benutzer auf diesem Produkt zur Verfügung stehen.      

Beispiel:
BSBS_ListPresets(12345, '4aa8fe15-d16c-23ba-e42b-86dc75a3ed09');
````  

````
BSBS_PlayPreset(integer $InstanzID, string $ProduktID, integer $Voreinstellung);  
Spielt eine Voreinstellung (Preset 1-6) auf dem Produkt ab.      

Werte für $Voreinstellung: 1 bis 6  

Beispiel:
BSBS_PlayPreset(12345, '4aa8fe15-d16c-23ba-e42b-86dc75a3ed09', 1);
````
  
````
BSBS_PlayAudioNotification(integer $InstanzID, string $ProduktID, string $AudioUrl, integer $Lautstärke);  
Spielt eine Audiobenachrichtigung ab.       

Werte für $AudioUrl: 'https://example.com/notification.mp3'  
Eine URL, die auf eine Audiodatei zeigt, die abgespielt wird.  
Die Datei unter dieser URL muss öffentlich zugänglich sein.  

Werte für $Lautstärke: 0 bis 100  
Der Lautstärkepegel, mit dem die Benachrichtigung abgespielt wird.  
Dieser Wert ist optional, wenn keine Lautstärke angegeben wird, wird die Benachrichtigung mit der gleichen Lautstärke wie der aktuelle Inhalt abgespielt.  

Beispiel:
BSBS_PlayAudioNotification(12345, '4aa8fe15-d16c-23ba-e42b-86dc75a3ed09', 'https://example.com/notification.mp3' ,15);
````