PDF417 Changelog
================

0.3.0 (2017-07-29)
------------------

* Fixed a bug in the character table where `g` would be decoded as `"` if
  preceeded by punctuation (#8) thanks @wotan192

0.2.0 (2016-05-05)
------------------

* Added support for new formats in ImageRenderer (tif, bmp, data-url) (#1)
* Fixed bug when encoded text started with numbers or bytes (#2)

0.1.2 (2014-12-26)
------------------

* Fixed an edge case in calculating Reed Solomon factors

0.1.1 (2014-12-26)
------------------

* Added validation of options in renderers.
* Fixed a bug in ImageRenderer where padding was not in bgColor, but white.
* Upgraded Intrevention/Image to v2.
* Added 'quality' option to ImageRenderer (used for JPG only).

0.1.0 (2014-12-24)
------------------

* Initial release
