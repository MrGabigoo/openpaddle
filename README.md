## openpaddle
FOSS scoreboard system for table-tennis written in PHP, MySQL, Vue.js, axios.js and a little bit of jQuery.

### About
openpaddle was commissioned by Léo Barès and Louis Mathieu for use at a Sciences Po event.
They needed a small application for ranking players at their event that would work on any computer.

Web hosting with PHP and a MySQL database being a cheap commodity nowadays, I chose to develop this application with the very lightweight and easy to use [CodeIgniter](https://codeigniter.com) web framework, in order to create a simple JSON API that would interact with a database.

The frontend was written in [Vue.js](https://vuejs.org) and [jQuery](https://jquery.com), and interacts with the JSON API using [axios.js](https://github.com/axios/axios).

### Why CodeIgniter?
This project was somewhat time-sensitive, so I decided to use a web framework rather than having to reinvent the wheel.

CodeIgniter is very easy to develop with but also to set up on any PHP-enabled web server. Since this application was originally meant to run on a first generation Raspberry Pi, I had to be sure that the Pi could run it without any performance issues. 

CodeIgniter seemed fit for the job since it's very lightweight, and I had already used it for a few projects (most notably a game project, [LostBot](https://lostbot.webdoy.com))

### Setup
If you want to install openpaddle on your own server, all you need to do is follow these simple steps:


##### 1. Clone this repo

##### 2. Database setup:
  * Create a database (if needed)
  * Grab the `op_games.sql` and `op_players.sql` files from `application/models/` and import them into your database

##### Application setup
  * Edit the database configuration file located at `application/config/database.php` and enter your database credentials
  * Edit the application's base URL used for AJAX calls in `application/views/home_view.php` on line **108**. _(Don't forget the trailing slash!)_
  
##### Use openpaddle!


### Contributing
This is a bit of a one-off project, but I'll gladly review any pull requests you send my way.

### License
openpaddle is licensed under the **GNU General Public License v3.0**. For more information, see [LICENSE](https://github.com/MrGabigoo/openpaddle/blob/master/LICENSE)
