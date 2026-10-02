#!"D:/ActivePerl/bin/perl"

##!"D:/Program Files/Aldec/Active-HDL 4.2/PERL/bin/Perl"
##!/usr/local/bin/perl
##!Perlbase/bin/Perl
##
##  printenv -- demo CGI program which just prints its environment
##
#print "HTTP/1.0 200 OK\n";
print "Content-type: text/plain\n\n";
foreach $var (sort(keys(%ENV))) {
    $val = $ENV{$var};
    $val =~ s|\n|\\n|g;
    $val =~ s|"|\\"|g;
    print "${var}=\"${val}\"\n";
}

## From apache cgi explanation
##
#     print "\n\n\n";
#     foreach $key (keys %ENV) {
#          print "$key --> $ENV{$key}\n";
#     }
#     print "\n\n\n";     
     
#	@query_input = <>;
#	print @query_input;
#	chomp (@query_input);
#
#	$query_string = join ('&', @query_input);
#	$query_string =~ s/\\(.)/sprintf ('%%%x', ord ($1))/eg;
#
#$self->_decode_url_encoded_data (\$query_string, 'form');