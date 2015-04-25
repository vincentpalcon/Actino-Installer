# Actino Installer
Actino Inc - Installer Script (help you to setup database and generate configuration).
Generated configuration will be at /class folder

## Quick Guide
You can change settings.xml to customized it.

### Example

* Setup Page Title and Copyright
```xml
    <title>ACTINO</title>
    <copyright>Actino Inc | Manage Strategic Change</copyright>
```	
* Setup source sql data
```xml
    <source>data.sql</source>
```	
* System Requirements
```xml
    <requires>
        <version>5.1.2</version>
        <extension name="curl" />
        <extension name="gd" />
        <extension name="mbstring" />
        <extension name="mcrypt" />
        <extension name="simplexml" />
        <extension name="zip" />
        <extension name="json" />
    </requires>
```		
* Language
```xml
    <languages>
        <default>en</default>
        <language id="en">
            <choose title="Language">
            	<option value="en">English</option>
            	<option value="fr">French</option>
            </choose>
        </language>
        <language id="fr">
            <choose title="Idioma">
            	 <option value="en">Anglais</option>
            	 <option value="fr">Français</option>
            </choose>
        </language>
    </languages>
```		
* Database Settings
```xml
    <values>
        <host>localhost</host>
        <database></database>
        <username></username>
        <prefix></prefix>
    </values>
```	
