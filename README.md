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
yum based systems
```
      sudo yum install netpbm
      sudo yum install ocrad
```


Composer
-

Add repository to `composer.json` file 
```
   "repositories": 
   [
      { "type": "vcs", "url": "https://github.com/xakepsoft/haba-lv-api" }
   ],
```
instruct composer to download the latest version
```
    "require":
    {
        "xakepsoft/haba-lv-api" : "@dev"
    }
```

Without Composer
-

Just download [HabaLV.php](https://raw.githubusercontent.com/xakepsoft/haba-lv-api/master/src/HabaLV.php) and include it in your php file
