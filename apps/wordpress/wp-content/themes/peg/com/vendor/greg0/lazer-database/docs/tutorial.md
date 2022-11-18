### Welcome

In this tutorial we will create simple news system.

### Create tables

First of all we must create our tables: `users`, `news`, `comments`, `tags`: 

#### Code:
```php
use Lazer\Classes\Database as Lazer; /* I will skip this line in rest of code samples */

Lazer::create('users', array(
    'name' => 'string',
    'email' => 'string',
));

Lazer::create('news', array(
    'topic' => 'string',
    'content' => 'string',
    'author_id' => 'integer'
));

Lazer::create('comments', array(
    'content' => 'string',
    'author_id' => 'integer',
    'news_id' => 'integer'
));

Lazer::create('tags', array(
    'name' => 'string'
));
```

### Create relations

Now we will create relations:
#### Code:
```php
use Lazer\Classes\Relation;

/* relations for News table */
Relation::table('news')->belongsTo('users')->localKey('author_id')->foreignKey('id')->setRelation();
Relation::table('news')->hasMany('comments')->localKey('id')->foreignKey('news_id')->setRelation();
Relation::table('news')->hasAndBelongsToMany('tags')->localKey('id')->foreignKey('id')->setRelation();

/* relations for Users table */
Relation::table('users')->hasMany('news')->localKey('id')->foreignKey('author_id')->setRelation();
Relation::table('users')->hasMany('comments')->localKey('id')->foreignKey('author_id')->setRelation();

/* relations for Comments table */
Relation::table('comments')->belongsTo('news')->localKey('news_id')->foreignKey('id')->setRelation();
Relation::table('comments')->belongsTo('users')->localKey('author_id')->foreignKey('id')->setRelation();

/* relations for Tags table */
Relation::table('tags')->hasAndBelongsToMany('news')->localKey('id')->foreignKey('id')->setRelation();
```

### Add some data

Put some data into tables

#### Users:
```php
$user = Lazer::table('users');

$user->name = 'Paul';
$user->email = 'paul@example.com';
$user->save();

$user->name = 'Kriss';
$user->email = 'kriss@example.com';
$user->save();

$user->name = 'John';
$user->email = 'john@example.com';
$user->save();

$user->name = 'Larry';
$user->email = 'larry@example.com';
$user->save();
```

#### News:

```php
$news = Lazer::table('news');

$news->topic = 'Lorem ipsum';
$news->content = 'Lorem ipsum dolor sit amet enim. Etiam ullamcorper. Suspendisse a pellentesque dui, non felis. Maecenas malesuada elit lectus felis, malesuada ultricies. Curabitur et ligula. Ut molestie a, ultricies porta urna. Vestibulum commodo volutpat a, convallis ac, laoreet enim. Phasellus fermentum in, dolor.';
$news->author_id = 1; /* John */
$news->save();

$news->topic = 'Some breaking news';
$news->content = 'Some content of breaking news. Pellentesque facilisis. Nulla imperdiet sit amet magna. Vestibulum dapibus, mauris nec malesuada fames ac turpis velit, rhoncus eu.';
$news->author_id = 3; /* Larry */
$news->save();
```

#### Tags:

```php
$tag = Lazer::table('tags');

$tag->name = 'news';
$tag->save();

$tag->name = 'breaking';
$tag->save();

$tag->name = 'lorem';
$tag->save();

$tag->name = 'ipsum';
$tag->save();
```

#### Comments:

```php
$commment = Lazer::table('comments');

$commment->content = 'I wrote fantastic news';
$commment->author_id = 1; /* John */
$commment->news_id = 1; /* "Lorem..." news */
$commment->save();

$commment->content = 'Lorem ipsum';
$commment->author_id = 2; /* Kriss */
$commment->news_id = 1; /* "Lorem..." news */
$commment->save();

$commment->content = 'Terrible';
$commment->author_id = 4; /* Paul */
$commment->news_id = 2; /* "Breaking..." news */
$commment->save();
```

Now we will insert records into junction table (created automatically) between `News` and `Tags`: 

#### Join tags to news:

```php
$junction = Relation::table('news')->with('tags')->getJunction();
$tag_join = Lazer::table($junction);

$tag_join->news_id = 1; /* "Lorem..." news */
$tag_join->tags_id = 3; /* "lorem" tag */
$tag_join->save();

$tag_join->news_id = 1;
$tag_join->tags_id = 4; /* "ipsum" tag */
$tag_join->save();

$tag_join->news_id = 1; /* "Lorem..." news */
$tag_join->tags_id = 1; /* "news" tag */
$tag_join->save();

$tag_join->news_id = 2; /* "Breaking..." news */
$tag_join->tags_id = 1; /* "news" tag */
$tag_join->save();

$tag_join->news_id = 2;
$tag_join->tags_id = 2; /* "breaking" tag */
$tag_join->save();
```

### Finish, display it!:

#### Code:
```php
$news = Lazer::table('news')->with('users')->with('tags')->with('comments')->with('comments:users')->findAll();

foreach($news as $post)
{
    $comments = $post->Comments; //->limit(1); // add limit
    // $comments = $comments->where('author_id', '=', 4); // To get specific user's comments only
    echo '<h1>'.$post->topic.'</h1>';
    echo '<h4>Author: '.$post->Users->name.'</h4>';   
    echo '<p>'.$post->content.'</p>';
    echo '<small>Tags: '.implode(', ', $post->Tags->findAll()->asArray(null, 'name')).'</small><br />';
    echo '<small>Comments: '.$comments->findAll()->count().'</small>';
    echo '<ul>';
    foreach($comments  as $comment)
    {
        echo '<li>';
            echo '<h5><a href="mailto:'.$comment->Users->email.'">'.$comment->Users->name.'</a>: </h5>';
            echo '<p>"'.$comment->content.'"</p>';
        echo '</li>';
    }
    echo '</ul>';
}
```
