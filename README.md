This is a fork of https://nasauber.de/opensource/b8/ . The main differences with it are:
 - Support of PHP PDO (the file is still called mysql.php due to limited time/laziness)
 - All tokens are now lowercase so that spammers can't evade the spam rules with different casings
 - Added a list of words to ignore (can be edited under b8/lexer/standard.php line 32, keep everything lowercase)
 - Avoids a database crash in the unlikely scenario that the script would try to insert the same token twice

As per the Mr. Leupold project, it is also dependency-free and simple to setup.

==========
b8: readme
==========

:Author: Tobias Leupold
:Homepage: http://nasauber.de/
:Contact: tobias.leupold@web.de
:Date: @LASTCHANGE@

.. contents:: Table of Contents

Description of b8
=================

What is b8?
-----------

b8 is a spam filter implemented in `PHP <http://www.php.net/>`__. It is intended to keep your weblog or guestbook spam-free. The filter can be used anywhere in your PHP code and tells you whether a text is spam or not, using statistical text analysis. What it does is: you give b8 a text and it returns a value between 0 and 1, saying it's ham when it's near 0 and saying it's spam when it's near 1. See `How does it work?`_ for details about this. |br|
To be able to do this, b8 first has to learn some spam and some ham (non-spam) texts. If it makes mistakes when classifying unknown texts or the result is not distinct enough, b8 can be told what the text actually is, getting better with each learned text.

b8 is a statistical spam filter. I'm not a mathematician, but as far as I can grasp it, the math used in b8 has not much to do with Bayes' theorem itself. So I call it a *statistical* spam filter, not a *Bayesian* one. Principally, It's a program like `Bogofilter <http://bogofilter.sourceforge.net/>`__ or `SpamBayes <http://spambayes.sourceforge.net/>`__, but it is not intended to classify emails. Therefore, the way b8 works is slightly different from email spam filters. See `What's different?`_ if you're interested in the details.

An example of what we're talking about here:

At the moment of this writing (november 2012), b8 has, since december 2006, classified 26869 guestbook entries and weblog comments on my homepage. 145 were ham. 76 spam texts (0.28 %) have been falsely rated as ham (false negatives) and I had to remove them manually. Only one single ham message has been falsely classified as spam (false positive) back in june 2010, but – in defense of b8 – this was the very first English ham text I got. Previously, each and every of the 15024 English texts posted has been spam. Texts with Chinese, Japanese or Cyrillic content (all spam either) did not appear until 2011. |br|
This results in a sensitivity of 99.72 % (the probability that a spam text will actually be rated as spam) and a specifity of 99.31 % (the probability that a ham text will actually be rated as ham) for my homepage. Before the one false positive, of course, the specifity has been 100 % ;-)

How does it work?
-----------------

In principle, b8 uses the math and technique described in Gary Robinson's articles "A Statistical Approach to the Spam Problem" [#statisticalapproach]_ and "Spam Detection" [#spamdetection]_. The "degeneration" method Paul Graham proposed in "Better Bayesian Filtering" [#betterbayesian]_ has also been implemented.

b8 cuts the text to classify to pieces, extracting stuff like email addresses, links and HTML tags and of course normal words. For each such token, it calculates a single probability for a text containing it being spam, based on what the filter has learned so far. When the token has not been seen before, b8 tries to find similar ones using "degeneration" and uses the most relevant value found. If really nothing is found, b8 assumes a default rating for this token for the further calculations. |br|
Then, b8 takes the most relevant values (which have a rating far from 0.5, which would mean we don't know what it is) and calculates the combined probability that the whole text is spam.

What do I need for it?
----------------------

Not much! You just need PHP 5 and a database to store the wordlist.

This version is specifically designed for MySQL / PDO PHP, it doesn't have the support for Berkeley DB / mysqli / PostgreSQL. If you need support for these database systems, please see this repository: https://github.com/byjg/b8

What's different?
-----------------

b8 has been designed to classify forum posts, weblog comments or guestbook entries, not emails. For this reason, it uses a slightly different technique than most of the other statistical spam filters out there use.

My experience was that spam entries on my weblog or guestbook were often quite short, sometimes just something like "123abc" as text and a link to a suspect homepage. Some spam bots don't even made a difference between e. g. the "name" and "text" fields and posted their text as email address, for example. Considering this, b8 just takes one string to classify, making no difference between "headers" and "text". |br|
The other thing is that most statistical spam filters count one token one time, no matter how often it appears in the text (as Paul Graham describes it in [#planforspam]_). b8 does count how often a token has been seen and learns resp. considers this. Why this? Because a text containing one link (no matter where it points to, just indicated by a "\h\t\t\p\:\/\/" or a "www.") might not be spam, but a text containing 20 links might be.

This means that b8 might be good for classifying weblog comments, guestbook entries or forum posts (I really think it is ;-) – but very likely, it will work quite poor when being used for something else like classifying emails. At least with the default lexer. But as said above, for this task, there are lots of very good filters out there to choose from.


Installation
============

Installing b8 on your server is quite easy:

1) Upload the whole folder to your server
2) Create the database table specified in install/setup_mysql.php

That's it ;-)

Configuration
=============

The configuration is passed as arrays when instantiating a new b8 object. Four arrays can be passed to b8. One containing b8's base configuration, one for the storage backend, one for the lexer and one for the degenerator. |br|

Not all values have to be set. When some values are missing, the default ones will be used. If you do use the default settings, you don't have to pass them to b8. But of course, if you want to set something in e. g. the fourth config array, but not in the third, you will have to pass an empty ``array()`` as third parameter anyway.

b8's base configuration
-----------------------

All these values can be set in the "config_b8" array (the first parameter) passed to b8. The name of the array doesn't matter (of course), it just has to be the first argument.

These are some basic settings telling b8 which backend classes to use:

    **storage**
        This defines which storage backend will be used to save b8's wordlist. Currently, three databases are supported: `Berkeley DB <http://oracle.com/technetwork/products/berkeleydb/downloads/index.html>`_ (``dba``), `MySQL <http://mysql.com/>`_ (``mysql`` and ``mysqli``) and `PostgreSQL <http://postgresql.org/>`_ (``postgresql``). An experimental backend for `SQLite <http://sqlite.org/>`_ resides in SVN trunk but has not reached release quality yet. The default is ``dba`` (string).

        *PDO (MySQL)*
            The MySQL relational database system is used very widely on the web and can also be used for storing b8's wordlist. This backend needs of course a running and accessable MySQL server and database. The backend uses the PDO PHP functions to interact with the database.

        See `Configuration of the storage backend`_ for the settings of the chosen backend.

    **lexer**
        The lexer class to be used. Defaults to ``default`` (string). |br|
        At the moment, only one lexer exists, so you probably don't want to change this unless you have written your own lexer.

    **degenerator**
        The degenerator class to be used. See `How does it work?`_ and [#betterbayesian]_ if you're interested in what "degeneration" is. Defaults to ``default`` (string). |br|
        At the moment, only one degenerator exists, so you probably don't want to change this unless you have written your own degenerator.

The following settings influence the mathematical internals of b8. If you want to experiment, feel free to play around with them; but be warned: wrong settings of these values will result in poor performance or could even "short-circuit" the filter. Leave these values as they are unless you know what you are doing.

The "Statistical discussion about b8" [#b8statistic]_ shows why the default values are the default ones.

    **use_relevant**
        This tells b8 how many tokens should be used to calculate the spamminess of a text. The default setting is ``15`` (integer). This seems to be a quite reasonable value. When using too many tokens, the filter will fail on texts filled with useless stuff or with passages from a newspaper, etc. not being very spammish. |br|
        The tokens counted multiple times (see above) are added in addition to this value. They don't replace other interesting tokens.

    **min_dev**
        This defines a minimum deviation from 0.5 that a token's rating must have to be considered when calculating the spamminess. Tokens with a rating closer to 0.5 than this value will simply be skipped. |br|
        If you don't want to use this feature, set this to ``0``. Defaults to ``0.2`` (float). Read [#b8statistic]_ before increasing this.

    **rob_x**
        This is Gary Robinson's *x* constant (cf. [#spamdetection]_). A completely unknown token will be rated with the value of ``rob_x``. The default ``0.5`` (float) seems to be quite reasonable, as we can't say if a token that also can't be rated by degeneration is good or bad. |br|
        If you receive much more spam than ham or vice versa, you could change this setting accordingly.

    **rob_s**
        This is Gary Robinson's *s* constant. This is essentially the probability that the *rob_x* value is correct for a completely unknown token. It will also shift the probability of rarely seen tokens towards this value. The default is ``0.3`` (float) |br|
        See [#spamdetection]_ for a closer description of the *s* constant and read [#b8statistic]_ for specific information about this constant in b8's algorithms.


Configuration of the lexer
--------------------------

The lexer disassembles the text we want to analyze to single words ("tokens"). The way it does this can be customized.

All the following values can be set in the "config_lexer" array (the third parameter) passed to b8. The name of the array doesn't matter (of course), it just has to be the third argument.

**min_size**
    The minimal length for a token to be considered when calculating the rating of a text. Defaults to ``3`` (integer).

**max_size**
    The maximal length for a token to be considered when calculating the rating of a text. Defaults to ``30`` (integer).

**allow_numbers**
    Should pure numbers also be considered? Defaults to ``false`` (boolean).

**get_uris**
    Look for URIs. Defaults to ``true`` (boolean).

**old_get_html**
    Extracts HTML. This is the old search function used up to b8 0.5.2. If you have an existing b8 installation and want the exactly same behaviour as before, use this. This function will probably removed in a future release. Defaults to ``true`` (boolean).

**get_html**
    Extracts HTML. This has been added in b8 0.6 and should work better then the "old_get_html" procedure. Defaults to ``false`` (boolean).

**get_bbcode**
    Extracts BBCode, which is often used in forums. Defaults to ``false`` (boolean).

Configuration of the degenerator
--------------------------------

When a token is not found in the database, b8 tries to find similar versions of that token. The degenerator provides these similar versions (cf. [#betterbayesian]_). The way it generates these "degenerates" can be customized.

All the following values can be set in the "config_degenerator" array (the fourth parameter) passed to b8. The name of the array doesn't matter (of course), it just has to be the fourth argument.

**multibyte**
    Use multibyte operations when searching for degenerated versions of an unknown token. When activating this, b8 needs PHP's ``mbstring`` module to work. Defaults to ``false`` (boolean).

**encoding**
    The internal encoding to use when doing multibyte operations. This will only be used when ``multibyte`` is set to ``true``. Defaults to ``UTF-8`` (string).

The difference of using or not using multibyte operations will only show up when non-latin-1 text is processed by b8. For example, if we have an unknown token ``HeLlO!``, the degenerator will provide the degenerated versions ``hello!``, ``HELLO!``, ``Hello!``, ``hello``, ``HELLO``, ``Hello`` and ``HeLlO``, no matter if multibyte operations are used or not.

When we have a non-latin-1 word, we may get a different result. For example, if we have the unknow token ``ПрИвЕт!``, the degenerator will only provide one degenerated version of it: ``ПрИвЕт``. Using multibyte operations, we get the same variants as with the latin-1 word: ``привет!``, ``ПРИВЕТ!``, ``Привет!``, ``привет``, ``ПРИВЕТ``, ``Привет`` and ``ПрИвЕт``.

Using multibyte operations will simply make the degenerator more effective.

Using b8
========

Now, that everything is configured, you can start to use b8. A sample script that shows what can be done with the filter can be found in ``example/``. Using this script, you can test how all this works before integrating b8 in your own scripts.

Before you can start, you have to setup a database so that b8 can store a wordlist.

Setting up a new database
-------------------------

Setting up a new MySQL table
-------------------------

The SQL file ``install/setup_pdo.sql`` contains both the ``CREATE`` statement for the wordlist table of b8 and the ``INSERT`` statements for adding the necessary internal variables.

Simply change the table name according to your needs (or leave it as it is ;-) and run the SQL to setup a MySQL b8 wordlist table.

Using b8 in your scripts
------------------------

Running b8 is very simple, first you need to include the file ``b8.php``

	require_once( 'b8.php' ); // Make sure to use the right folder

Then you can call it like this:
	
 	try {

  		$b8 = new b8\b8( array( 'storage' => 'mysql' ), array( 'table' => 'b8_wordlist', 'resource' => $conn ) );

		// b8 actions here

 	} catch( Exception $e ) {
        print_r( $e );
    }

Note that ``$conn`` should be a valid PDO Object that connects to the MySQL database and ``b8_wordlist`` should be replaced by the actual name of your table.

The full script should then look like this:

	require_once( 'b8.php' );

	try {

  		$b8 = new b8\b8( array( 'storage' => 'mysql' ), array( 'table' => 'b8_wordlist', 'resource' => $conn ) );

		$b8->learn( 'India', 'spam' ); // These are samples, you will need to feed spam data to b8 depending on the context that you are using it in (for instance get the body of a spam email, strip the HTML tags and feed it to b8)
		$b8->learn( 'Business development', 'spam' ); // These are samples, you will need to feed spam data to b8 depending on the context that you are using it in (for instance get the body of a spam email, strip the HTML tags and feed it to b8)
 		$b8->learn( 'outsource', 'spam' ); // These are samples, you will need to feed spam data to b8 depending on the context that you are using it in (for instance get the body of a spam email, strip the HTML tags and feed it to b8)
		$b8->learn( 'Manektech', 'spam' ); // These are samples, you will need to feed spam data to b8 depending on the context that you are using it in (for instance get the body of a spam email, strip the HTML tags and feed it to b8)
		$b8->learn( 'Kenscio', 'spam' ); // These are samples, you will need to feed spam data to b8 depending on the context that you are using it in (for instance get the body of a spam email, strip the HTML tags and feed it to b8)

		$b8->classify( 'Hi Arthur, I still can’t see my landing page in the promotion tabs. Thanks James Dean' ); // This is a sample text, you would replace it with the text that you want to classify as spam/ham

 	} catch( Exception $e ) {
        print_r( $e ); // Displays errors, you may want to add a handler here
    }

By default b8 provides three functions in an object oriented way (called e. g. via ``$b8->classify($text)``):

**classify($text)**
    This function takes the text ``$text`` (string), calculates it's probability for being spam and returns it in the form of a value between 0 and 1 (float). |br|
    A value close to 0 says the text is more likely ham and a value close to 1 says the text is more likely spam. What to do with this value is *your* business ;-) See also `Tips on operation`_ below.

**learn($text, $category)**
    This saves the text ``$text`` (string) in the category ``$category`` (b8 constant, either ``b8::HAM`` or ``b8::SPAM``).

**unlearn($text, $category)**
    You don't need this function in normal operation. It just exists to delete a text from a category in which is has been stored accidentally before. It deletes the text ``$text`` (string) from the category ``$category`` (b8 constant, either ``b8::HAM`` or ``b8::SPAM``). |br|
    **Don't delete a spam text from ham after saving it in spam or vice versa, as long you don't have stored it accidentally in the wrong category before!** This will *not* improve performance, quite the opposite! The filter will always try to remove texts from the ham or spam data, even if they have never been stored there. The counters for tokens which are found will be decreased or the word will be deleted and the non-existing words will simply be ignored. But always, the text counter for the respective category will be decreased by 1 and will eventually reach 0. Consequently, the ham-spam texts proportion will become distorted, deteriorating the performance of b8's algorithms.

Tips on operation
=================

Before b8 can decide whether a text is spam or ham, you have to tell it what you consider as spam or ham. At least one learned spam or one learned ham text is needed to calculate anything. With nothing learned, b8 will rate everything with 0.5 (or whatever ``rob_x`` has been set to). To get good ratings, you need both learned ham and learned spam texts, the more the better. |br|
What's considered as ham or spam can be very different, depending on the operation site. On my homepage, practically each and every text posted in English or using non-latin-1 letters is spam. On an English or Russian homepage, this will be not the case. So I think it's not really meaningful to provide some "spam data" to start. Just train b8 with "your" spam and ham.

For the practical use, I advise to give the filter all data availible. E. g. name, email address, homepage and of course the text itself should be assembled in a variable (e. g. separated with an ``\n`` or just a space or tab after each block) and then be classified. The learning should also be done with all data availible. |br|
Saving the IP address is probably only meaningful for spam entries, because spammers often use the same IP address multiple times. In principle, you can leave out the IP of ham entries.

You can use b8 e. g. in a guestbook script and let it classify the text before saving it. Everyone has to decide which rating is necessary to classify a text as "spam", but a rating of >= 0.8 seems to be reasonable for me. If one expects the spam to be in another language that the ham entries or the spams are very short normally, one could also think about a limit of 0.7. |br|
The email filters out there mostly use > 0.9 or even > 0.99; but keep in mind that they have way more data to analyze in most of the cases. A guestbook entry may be quite short, especially when it's spam.

In my opinion, an autolearn function is very handy. I save spam messages with a rating higher than 0.7 but less than 0.9 automatically as spam. I don't do this with ham messages in an automated way to prevent the filter from saving a false negative as ham and then classifying and learning all the spam as ham when I'm on holidays ;-)

Learning spam or ham that has already been rated very high or low will not make spam detection better (as b8 already could classify the text correctly!) but probably only blow the database. So don't do that.

Closing
=======

So … that's it. Thanks for using b8! If you find a bug or have an idea how to make b8 better, let me know. I'm also always looking forward to hear from people using b8 and I'm curious where it's used :-)

References
==========

.. [#planforspam] Paul Graham, *A Plan For Spam* (http://paulgraham.com/spam.html)
.. [#betterbayesian] Paul Graham, *Better Bayesian Filtering* (http://paulgraham.com/better.html)
.. [#spamdetection] Gary Robinson, *Spam Detection* (http://radio.weblogs.com/0101454/stories/2002/09/16/spamDetection.html)
.. [#statisticalapproach] *A Statistical Approach to the Spam Problem* (http://linuxjournal.com/article/6467)
.. [#b8statistic] Tobias Leupold, *Statistical discussion about b8* (http://nasauber.de/opensource/b8/discussion/)

Appendix
========

FAQ
---

What about more than two categories?
````````````````````````````````````

I wrote b8 with the `KISS principle <http://en.wikipedia.org/wiki/KISS_principle>`__ in mind. For the "end-user", we have a class with almost no setup to do that can do three things: classify a text, learn a text and un-learn a text. Normally, there's no need to un-learn a text, so essentially, there are only two functions we need for the everyday use. |br|
This simplicity is only possible because b8 only knows two categories and tells you, in one float number between 0 and 1, if a given texts rather fits in the first or the second category. If we would support multiple categories, more work would have to be done and things would become more complicated. One would have to setup the categories, have another database layout (perhaps making it mandatory to have SQL) and one float number would not be sufficient to describe b8's output, so more code would be needed – even outside of b8.

All the code, the database layout and particularly the math is intended to do exactly one thing: distinguish between two categories. I think it would be a lot of work to change b8 so that it would support more than two categories. Probably, this is possible to do, but don't ask me in which way we would have to change the math to get multiple-category support ;-) |br|
Apart from this I do believe that most people using b8 don't want or need multiple categories. They just want to know if a text is spam or not, don't they? I do, at least ;-)

But let's think about the multiple-category thing. How would we calculate a rating for more than two categories? If we had a third one, let's call it "`Treet <http://en.wikipedia.org/wiki/Treet>`__", how would we calculate a rating? We could calculate three different ratings. One for "Ham", one for "Spam" and one for "Treet" and choose the highest one to tell the user what category fits best for the text. This could be done by using a small wrapper script using three instances of b8 as-is and three different databases, each containing texts being "Ham", "Spam", "Treet" and the respective counterparts. |br|
But here's the problem: if we have "Ham" and "Spam", "Spam" is the counterpart of "Ham". But what's the counterpart of "Spam" if we have more than one additional category? Where do the "Non-Ham", "Non-Spam" and "Non-Treet" texts come from?

Another approach, a direct calculation of more than two probabilities (the "Ham" probability is simply 1 minus the "Spam" probability, so we actually get two probabilities with the return value of b8) out of one database would require big changes in b8's structure and math.

There's a project called `PHPNaiveBayesianFilter <http://xhtml.net/scripts/PHPNaiveBayesianFilter>`__ which supports multiple categories by default. The author calls his software "Version 1.0", but I think this is the very first release, not a stable or mature one. The most recent change of that release dates back to 2003 according to the "changed" date of the files inside the zip archive, so probably, this project is dead or has never been alive and under active development at all. |br|
Actually, I played around with that code but the results weren't really good, so I decided to write my own spam filter from scratch back in early 2006 ;-)

All in all, there seems to be no easy way to implement multiple (meaning more than two) categories using b8's current code base and probably, b8 will never support more than two categories. Perhaps, a fork or a complete re-write would  be better than implementing such a feature. Anyway, I don't close my mind to multiple categories in b8. Feel free to tell me how multiple categories could be implementented in b8 or how a multiple-category version using the same code base (sharing a common abstract class?) could be written.

What about a list with words to ignore?
```````````````````````````````````````

This version features a list of words to ignore. You can complete it by editing the line 32 of lexer/standard.php 

Why is it called "b8"?
``````````````````````

The initial name for the filter was (damn creative!) "bayes-php". There were two main reasons for searching another name: 1. "bayes-php" sucks. 2. the `PHP License <http://php.net/license/3_01.txt>`_ says the PHP guys do not like when the name of a script written in PHP contains the word "PHP". Read the `License FAQ <http://www.php.net/license/index.php#faq-lic>`_ for a reasonable argumentation about this.

Luckily, `Tobias Lang <http://langt.net/>`_ proposed the new name "b8". And these are the reasons why I chose this name:

- "bayes-php" is a "b" followed by 8 letters.
- "b8" is short and handy. Additionally, there was no program with the name "b8" or "bate"
- The English verb "to bate" means "to decrease" – and that's what b8 does: it decreases the number of spam entries in your weblog or guestbook!
- "b8" just sounds way cooler than "bayes-php" ;-)

The database layout
-------------------

The database layout is quite simple. It's essentially just a key-value pair for everything stored. There are two "internal" variables stored as normal tokens. A lexer must not provide a token starting with ``b8*``, otherwise, we will probably get collisions. The internal tokens are:

**b8*dbversion**
    This indicates the database's version.

**b8*texts**
    The number of ham and spam texts learned.

Each "normal" token is stored with it's literal name as the key and it's data as the value. The backends store the token's data in a different way. The DBA backend simply stores a string containing both values separated by a space character. The SQL backends store the counters in different columns.

A database query is always done by searching for a token's name, never for a count value.

.. |br| raw:: html

   <br />

.. section-numbering::

.. |date| date::
