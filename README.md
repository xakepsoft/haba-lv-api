# Swedbank API
Currently only Latvian SwedBank branch is supported. 
Support for EE and LT will be added in near future.

Installation
-------------

Make sure the curl extension (`php5-curl`) is installed

      sudo apt-get install php5-curl
      
      sudo yum install php5-curl


These 2 tools are also required: ( `netpbm`, `ocrad` )
```
      sudo apt-get install netpbm
      sudo apt-get install ocrad
```
or for yum based systems
```
      sudo yum install netpbm
      sudo yum install ocrad
```


Composer
-

Add repository to your `composer.json` file 
```
   "repositories": 
   [
      { "type": "vcs", "url": "https://github.com/xakepsoft/haba-lv-api" }
   ],
```
and instruct composer to download the latest version
```
    "require":
    {
        "xakepsoft/haba-lv-api" : "@dev"
    }
```
