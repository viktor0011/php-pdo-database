# php-pdo-database
Connection to database in PHP using PDO

A database class for PHP-MySQL which uses the PDO extension.
I used singleton pattern, if this pattern doesn't work for your project, you can change it easily.
## To use the class
#### 1. Edit the database settings in the index.php
### Note if PDO is loading slow change localhost to -> 127.0.0.1 !

#### 2. Require the class in your project
```php
<?php
require("database.php");
```
#### 3.  Logs - Modify the read/write rights of the logs folder

The log file is a simple plain text file with the current date('year-month-day') as filename.

## Examples
Below some examples of the basic functions of the database class.
#### The persons table 
| id | firstname | lastname | sex | age
|:-----------:|:------------:|:------------:|:------------:|:------------:|
| 1       |        John |     Doe    | M | 19
| 2       |        Bob  |     Black    | M | 41
| 3       |        Zoe  |     Chan    | F | 20
| 4       |        Kona |     Khan    | M | 14
| 5       |        Kader|     Khan    | M | 56

#### Fetching everything from the table
```php
<?php
// Fetch whole table
$persons = $this->query("SELECT * FROM persons");
```
#### Fetching with Bindings (ANTI-SQL-INJECTION):
Binding parameters is the best way to prevent SQL injection. The class prepares your SQL query and binds the parameters
afterwards.

There are three different ways to bind parameters.
```php
<?php
// 1. Read friendly method  
$this->bind("id","1");
$this->bind("firstname","John");
$person   =  $this->query("SELECT * FROM Persons WHERE firstname = :firstname AND id = :id");

// 2. Bind more parameters
$this->bindMore(array("firstname"=>"John","id"=>"1"));
$person   =  $this->query("SELECT * FROM Persons WHERE firstname = :firstname AND id = :id"));

// 3. Or just give the parameters to the method
$person   =  $this->query("SELECT * FROM Persons WHERE firstname = :firstname",array("firstname"=>"John","id"=>"1"));
```

#### Fetching Row:
This method always returns only 1 row.
```php
<?php
// Fetch a row
$ages     =  $this->row("SELECT * FROM Persons WHERE  id = :id", array("id"=>"1"));
```
##### Result
| id | firstname | lastname | sex | age
|:-----------:|:------------:|:------------:|:------------:|:------------:|
| 1       |        John |     Doe    | M | 19
#### Fetching Single Value:
This method returns only one single value of a record.
```php
<?php
// Fetch one single value
$this->bind("id","3");
$firstname = $this->single("SELECT firstname FROM Persons WHERE id = :id");
```
##### Result
|firstname
|:------------:
| Zoe
#### Fetching Column:
```php
<?php
// Fetch a column
$names    =  $this->column("SELECT Firstname FROM Persons");
```
##### Result
|firstname | 
|:-----------:
|        John 
|        Bob  
|        Zoe  
|        Kona 
|        Kader
### Delete / Update / Insert
When executing the delete, update, or insert statement by using the query method the affected rows will be returned.
```php
<?php

// Delete
$delete   =  $this->query("DELETE FROM Persons WHERE Id = :id", array("id"=>"1"));

// Update
$update   =  $this->query("UPDATE Persons SET firstname = :f WHERE Id = :id", array("f"=>"Jan","id"=>"32"));

// Insert
$insert   =  $this->query("INSERT INTO Persons(Firstname,Age) VALUES(:f,:age)", array("f"=>"Vivek","age"=>"20"));

// Do something with the data 
if($insert > 0 ) {
  return 'Succesfully created a new person !';
}

```
## Method parameters
Every method which executes a query has the optional parameter called bindings.

The <i>row</i> and the <i>query</i> method have a third optional parameter  which is the fetch style.
The default fetch style is <i>PDO::FETCH_ASSOC</i> which returns an associative array.

Here an example :

```php
<?php
  // Fetch style as third parameter
  $person_num =     $this->row("SELECT * FROM Persons WHERE id = :id", array("id"=>"1"), PDO::FETCH_NUM);

  print_r($person_num);
  // Array ( [0] => 1 [1] => Johny [2] => Doe [3] => M [4] => 19 )
    
```
More info about the PDO fetchstyle : http://php.net/manual/en/pdostatement.fetch.php

