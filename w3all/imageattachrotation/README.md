# phpBB attachments images rotation
For jpg/jpeg, gif and png attachments files
## Version 1.0.4

Tested under 3.3.0> ( 3.2.0> should also work fine, but not tested ) ( 3.1.0> not tested )

## Install

Copy the "w3all" folder into phpBB/ext/

So you'll have: phpBB/ext/w3all/w3imagerotation

Set ACP option "Recompile stale style components" to yes

Go to "ACP" > "Customise" > "Extensions" and enable the "Attachments images rotation" extension

Test things may doing a test post

If all ok, reset "Recompile stale style components" to no

Done

## How do i can style the rotation popup?

It should fit your theme, anyway you can easily change and style it into:

## HTML:
/ext/w3all/imageattachrotation/styles/prosilver/template/event/overall_footer_body_after.html

(note that you (maybe) do NOT have to change w3classes and w3ids for html elements, or the javascript code will not work) 

## CSS:

/ext/w3all/imageattachrotation/styles/prosilver/template/css/style.css


## Update/install: 

If updating, disable the old version and delete data into ACP Extensions Manager

Upload and copy or overwrite all files as above explained

Set ACP option "Recompile stale style components" to yes

Enable the Attachments images rotation extension

Test things may doing a test post

If all ok, reset "Recompile stale style components" to no

Done

## Missing older versions?
### Check all images attachments releases at [axew3.com](https://www.axew3.com):
### [phpbb images attachments rotation - all releases](https://www.axew3.com/w3/forums/viewtopic.php?f=20&t=1580 "phpbb images attachments rotation")


## License

[GPLv2](license.txt)
