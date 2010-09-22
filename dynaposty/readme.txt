=== DynaPosty ===

Contributors: Mobiah
Tags: dynamic, dynamic content, ppc tools, dynamic targeting, landing pages, dynamic landers, shortcodes, mobiah
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: 0.6

DynaPosty lets you define url variables and create shortcodes for corresponding dynamic content fields.

Translation: Easiest. Dynamic. Landers. Ever.


== Description ==

DynaPosty allows you to create and manage custom shortcodes and datasets to give WordPress the added functionality of dynamic landing pages.

First, you can identify or create a url variable that will be used to associate each visitor with a particular message.

Next, you can set cookies so that each individual user is associated with that particular message, even if they leave your site and return later.

DynaPosty has a very intuitive interface that allows you to create datasets, define shortcodes, and generally create and maintain all sorts of information that can be injected into your pages and posts. Just put shortcodes where you want dynamic content, then your user's url variable will be correlated with a specific dataset, and they'll see your custom targeted data.

An example: Let's say you are running a pay-per-click campaign for two of your products. You can create the destination url for the click ads, and include a variable that will indicate which product the user is interested in. In this case, the variable is "sixpack," but you can just as easily use numbers as real words.

Have Lots of Cats?
Our six pack kitten carriers are super cool 
and covered in awesomesauce.
http://kittencarrierz.com/?product=sixpack

When DynaPosty recognizes the url variable, it will inject the associated content (that you have already created) into every page where you have added shortcodes.

So, to you, the admin, a post headline might look like this:

Welcome to KittenCarriers! We're currently running a special on [product], offering [discount] on all items shipped before [date]!

However, with the magic of DynaPosty and the content you've created, the user will see:

Welcome to KittenCarriers! We're currently running a special on Kitten Six Packs, offering 15% on all items shipped before Friday!

Pretty darn slick, wouldn't you say?

Also, just in case you were wondering, there's also a default dataset, so that if the user somehow wanders to that page without a url variable or a cookie, they will see a generic version of the headline, like this:

Welcome to KittenCarriers! We're currently running a special on all cat products, offering free shipping on all items shipped before Friday!

If you have LOTS of campaigns running and you have a .csv (comma separated values) file containing all your value sets and shortcode values, you can upload.

We think this is pretty neat. Hopefully, you do, too.


== Installation ==

Step 1: Download the plugin files from mobiah.com/dynaposty/download/ or from wordpress.org/extend/plugins/dynaposty
Step 2: Upload the wp-dynaposty folder to your `/wp-content/plugins/` directory
Step 3: Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

Q: What url variable should I use?

A: This depends on your traffic sources. Google AdWords, for example, has some default variables that you can use here just as easily. Similarly, you can add create your own, simply by adding a ? to an existing url.  The basic format is this: ?content=variable.  In practice, you would probably want to define "content" as something relevant to your goal, like "product" or "source" and then the "variable" would be either a name or a number that correlates with a dataset in your dynamic content. 

Q: What are the limits of use?

A: Right now, there are no limits of use. There may be a day when we charge for especially heavy usage or multiple variable support or something like that, but not now.

Q: What happens if the user doesn't have a cookie or url variable but gets to a page with DynaPosty shortcodes?

A: DynaPosty requires that you establish default values for each shortcode that you create. That way, if any user doesn't have a cookie or url variable, they will still see content that makes sense and is complete.

Q: I see a grid of text.  What do I do?

A: Any field you see in the grid which is not bold, is editable.  Just click it to edit.

Q: Why are some of the rows blue?

A: The blue rows are special.  The "shortcode" row contains all the shortcodes you will use in your Post/Page content.  If the shortcode in this row is "location", then in your Posts/Pages, you would enter [location].  But don't worry.  There will be a dynaposty button which helps you insert the codes automatically.  The "Default Values" row is what will will be replaced when DynaPosty doesn't recognize the URL Variable Value, or the URL Variable just isn't in the URL at all.


== Upgrade Notice ==

Version 0.5 is the first full version to be released.  


== Changelog ==

0.5: 

Initial release


== License ==

Licensed under the GNU General Public License
Version 3, 29 June 2007
Full text available at: http://www.gnu.org/licenses/gpl.txt


== Contact ==

hello@mobiah.com