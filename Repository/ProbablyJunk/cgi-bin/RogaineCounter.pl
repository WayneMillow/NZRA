#!/usr/local/bin/perl
#
########   ORIGINAL  ########
# (c) 1998, 1999 by "Soenke J. Peters" <peters@simprovement.com>
# Version 1.00 (1999-02-11)
#
# This WAS a graphical counter that is called from the <img> tag.
# The only external resource is it's data file.
# It produces the count in GIF or XBM format.
#
########   RogaineCounter.pl   ###  WKK.. 7 April 2002
#
# WKK... The original program displayed a counter on the page. This is
# a considerably modified version that just increments a counter in a data
# file specified by the variable $counterFile. There is a count in the file
# for each page in which the counter is inserted.
#
# The syntax to insert the counter in a page is:
# <img src="/cgi-bin/RogaineCounter.pl" width=1 height=1>
#
# The document to be counted is accessed as $ENV{'HTTP_REFERER'}
#
# Make sure the protection on the counter data file allows writing 
# from this script.
#
######################################################################

$counterFile = "cgi-bin/data/RogaineCount";   #WKK... the location of the data file

######################################################################

   $|=1;	# flush STDOUT
   $countedPage = $ENV{'HTTP_REFERER'};

# Crude stripping of host name for now.
  $countedPage =~ s|http://||i;
  $countedPage =~ s|rogaine\.org\.nz/||i;
  $countedPage =~ s|www\.||i;

  if (-e $counterFile) {
    open(COUNT,"<$counterFile") or die("Can't open $counterFile: $!\n");
    flock(COUNT,2);seek(COUNT,0,0);
  }

  while (<COUNT>) {
    chomp $_;
    ($Page, $counts)=split(/=/,$_);
    $hashy{$Page}=$counts;
  }
  flock(COUNT,8);
  close(COUNT);
    if (! defined $hashy{$countedPage}) {
      $hashy{$countedPage}=0;
    }
    $hashy{$countedPage}++;

  open(COUNT,">$counterFile") || die "$0: can\'t open $counterFile: $!\n";
  flock(COUNT,2);seek(COUNT,0,0);
  while (($Page, $counts) = each %hashy) {
    print COUNT $Page.'='.$counts."\n";
  }

  flock(COUNT,8);
  close(COUNT);

exit(0);