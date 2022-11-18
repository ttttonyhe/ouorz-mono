### Welcome

Hi, on this page you will find some example of use my database.

### Find All

##### Query:
```php
$result = Lazer::table('users')->findAll();
foreach($result as $row)
{
    print_r($row);
}
```
### Limit

##### Query:
```php
Lazer::table('users')->limit(5)->findAll(); /* Get five records */
Lazer::table('users')->limit(10, 5)->findAll(); /* Get five records from 10th */
```
### Order By

##### Query:
```php
Lazer::table('users')->orderBy('id')->findAll();
Lazer::table('users')->orderBy('id', 'DESC')->findAll();
Lazer::table('users')->orderBy('category')->orderBy('id')->findAll(); /* Order by multiple fields */
```
### Where

##### Query:
```php
Lazer::table('users')->where('id', '=', 1)->findAll();
Lazer::table('users')->where('id', '>', 4)->findAll();
Lazer::table('users')->where('id', 'IN', array(1, 3, 6, 7))->findAll();
Lazer::table('users')->where('id', '>=', 2)->andWhere('id', '<=', 7)->findAll();
Lazer::table('users')->where('id', '=', 1)->orWhere('id', '=', 3)->findAll();
Lazer::table('users')->where('name', 'LIKE', 'Lar%')->findAll();
Lazer::table('users')->where('name', 'LIKE', '%ry')->findAll();
Lazer::table('users')->where('name', 'LIKE', '%a%')->findAll();
```
### Group By

##### Query:
```php
Lazer::table('news')->groupBy('category_id')->findAll();
```
### Count

##### Query:
```php
Lazer::table('users')->count(); /* Returns integer 0 */

Lazer::table('users')->findAll()->count(); /* Number of rows */

$users = Lazer::table('users')->findAll();
count($users); /* Number of rows */
```
You can use it with rest of methods
##### Query:
```php
Lazer::table('news')->where('id', '=', 2)->findAll()->count();
Lazer::table('news')->groupBy('category_id')->findAll()->count();
```
### As Array

 Use when you want to get array with results, not an object to iterate. 
##### Query:
```php
Lazer::table('users')->findAll()->asArray();
Lazer::table('users')->findAll()->asArray('id'); /* key of row will be an ID */
Lazer::table('users')->findAll()->asArray(null, 'id'); /* value of row will be an ID */
Lazer::table('users')->findAll()->asArray('id', 'name'); /* key of row will be an ID and value will be a name of user */
```
### With (JOIN)

<b>Caution! First letter of relationed table name is always uppercase.</b>

For example you can get News with it Comments. 
##### Query:
```php
$news = Lazer::table('news')->with('comments')->findAll();
foreach($news as $post)
{
    print_r($post);

    $comments = $post->Comments->findAll();
    foreach($comments as $comment)
    {
        print_r($comment);
    }
}
```

Also you can get News with it Author, Comments and each comment with it author
##### Query:
```php
$news = Lazer::table('news')->with('users')->with('comments')->with('comments:users')->findAll();
foreach($news as $post)
{
    print_r($post->Users->name); /* news author name */

    $comments = $post->Comments->findAll(); /* news comments */
    foreach($comments as $comment)
    {
        print_r($comment->Users->name); /* comment author name */
    }
}
```
In queries you can use all of features, simple example
```php
$post->Comments->orderBy('author_id')->limit(5)->findAll(); /* news comments */
```

### Conclusion

Of course all of these examples can be used together
```php
Lazer::table('users')->with('comments')->where('id', '!=', 1)->orderBy('name')->limit(15)->findAll()->asArray();
``` 