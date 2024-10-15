
## Version 1.0.8
just fix the file 
/w3all/imageattachrotation/styles/all/template/event/overall_header_head_append.html
so to correctly load the css file that style the rotation popup.
Works on all themes.

## Version 1.0.7

## Note that you'll find out always the last and improved version here:
## https://www.axew3.com/w3/forums/viewtopic.php?t=1639

# phpBB attachments images rotation
For jpg/jpeg, gif, png, webp attachments files
## Version 1.0.6

Tested under 3.3.13 > ( 3.2.0> should also work fine, but not tested ) ( 3.1.0> not tested )
### require Php version 7 or better

## Update/install: 

If updating, disable the old version and delete data into ACP Extensions Manager

Remove the folder imageattachrotation (or whatever it was nemed before) into
    
    /ext/w3all/

then follow installing the new 1.0.6

## Install

Copy the "w3all" folder into phpBB/ext/

So you'll have: phpBB/ext/w3all/imageattachrotation

Go to "ACP" > "Customise" > "Extensions" and enable the "Attachments images rotation" extension

Done

## How do i can style the rotation popup?

It should fit your theme, anyway you can easily change and style it into:

## HTML:
    
    /ext/w3all/imageattachrotation/styles/all/template/event/overall_footer_body_after.html

note that maybe you do NOT have to change w3classes and w3id for html elements, almost these used by javascript on same file, or the js code will not work.

The html code snippet is on top of the file

## CSS:

    /ext/w3all/imageattachrotation/styles/all/template/css/style.css


## How do i can choose to prepend or append the rotate icon?

    /ext/w3all/imageattachrotation/styles/all/template/event/overall_footer_body_after.html

search for line (+- on top): 

     var Pw3A = 1; // 0 Append icon, 1 Prepend icon (require to rebuild the phpBB template)
     

## Questions?
### [Ask on topic at phpBB.com](https://www.phpbb.com/community/viewtopic.php?f=456&t=2569976 "phpBB.com help topic")

## Check new instructions and download, also here:
### [phpBB images attachments rotation - 1.0.4](https://www.axew3.com/w3/forums/viewtopic.php?f=20&t=1639 "phpbb images attachments rotation")

## Missing older versions?
### Check all images attachments releases at [axew3.com](https://www.axew3.com):
### [phpbb images attachments rotation - all releases](https://www.axew3.com/w3/forums/viewtopic.php?f=20&t=1580 "phpbb images attachments rotation")


## License

[GPLv2](license.txt)



