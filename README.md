SSB - Simple Social Board
----
A Simple social media board script coded in php that runs on flatfile databases. It requires 
no setup (except setting properties in the config.php script). SSB is released 
under the CC-BY-NC-SA v3.0 unported.

SSB was written by Chris Dorman, 2012-2020 <https://cddo.cf>


ChangeLog
----
8/3/2020 - v1.2.2 - hotfix
* Private and public chat CSS updates
* Bugfix for user profile avatars not loading on pages when not logged in
* Add "my profile" button on navbar

8/3/2020 - v1.2.1 - feature update
* Added user settings panel for changing account passwords and avatar images.
* Display profile images in posts and user profile page.
* Bug fixes for unauthorized posts by users not friends with people when post links are directly connected to.
* Large CSS changes, more mobile device friendly. More changes to come.

8/2/2020 - v1.1.2 - hotfix
* Minor tweaks
* Another duplicate friend request bugfix

8/2/2020 - v1.1.1 - hotfix
* Mobile CSS and viewport tweaks
* Couple of missed bug tweaks

8/2/2020 - v1.1.0
* Added friend to friend private messaging
* Fixed a few friend request bugs
   -- Look for self sent friend requests and block
   -- Look for and block already accepted or pending friend requests
* Friend request and new message notifications with wipe function.
* Version bump, new ideas include:
   -- Owner / admin post removal
   -- Video media support
   -- Possible remove of public chat
   -- Password change (done)
   -- Profile pictures (done)
   -- Bot verification prompt for registeration.

8/2/2020 - v1.0.3 - hotfix
* After live version went online, noticed a bug with accepting friend requests multiple times
* Check to see if public user is already followed by user.

8/2/2020 - v1.0.2
* Added private and public accounts for public figures, pages, meme groups.

8/2/2020 - v1.0.1 - hotfix
* Felt it was needed to finish the about page.
* A couple UI tweaks, will probably have more minor version releases but meh.

8/1/2020 - v1.0.0
* Cleaned up functions, added more functions
* View profiles from friends list, user info and feed.
* Image upload capabilities.
* CSS clean up, still things to be done.
* Fully functioning public web chat!
* Considered operational, and beta released!

8/1/2020 - v0.2.0
* Working home feed with personal and friends posts in order by date newest to oldest!
* Public chat room with short term message buffer room. Great for chitchat.
* Personal messaging in progress.
* CSS clean up, added FontAwesome.
* Added form input BB code parsing!
* Known bugs: can send multi friend requests and spam another users notification box.

8/1/2020 - v0.1.0
* Working friends list to be incorporated into each users news feed output (in dev)
* Some more CSS cleaning, more streamline website. More mobile friendly 
* NOTE: I'm just pumping out work on this LOL

8/1/2020 - v0.0.2
* Reconstructed posting database for friends list and feed processing
* Separated some files (CSS, and functions within index.php)
* More CSS clean up.
* User base is also capable of replying, and posting. Feed still not operational.

8/1/2020 - v0.0.1
* Official release of SSB, now known Simple Social Board.
* Added userbase to database.
* Separated form functions into separate PHP doc for cleanliness.
* CSS and HTML modifications
* NOTE: Feed is public across all users currently. Working on friends system
* NOTE: Friends list and messaging still in progress.

2/1/2014 -
	Little fixing up
