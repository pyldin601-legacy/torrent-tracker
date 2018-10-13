#!/usr/bin/perl

use Convert::Bencode qw(bencode bdecode);
use FindBin qw($Bin);
use DBI;
use Encode;
use Encode::Detect::Detector;
use threads;
use threads::shared;
use Number::Format;
use File::Path qw(make_path);
use URI::Escape;
use File::Copy;

use LWP::Simple;

fork and exit;

my $nf = new Number::Format %args;

my $threads_limit = 8;

my @threads = ();
my @tqueue :shared = ();

my @trackers = (
	'http://retracker.local/announce',
#	'http://192.168.1.20:50000/announce.php'
#	'udp://tracker.publicbt.com:80',
#	'http://tracker.thepiratebay.org/announce',
#	'http://secure.pow7.com/announce',
#	'http://pow7.com/announce',
#	'http://fr33dom.h33t.com:3310/announce',
#	'http://exodus.desync.com:6969/announce',
#	'http://10.rarbg.com/announce',
#	'http://ix.rutracker.net/ann?uk=VInXMBVKp0'
#	'udp://tracker.openbittorrent.com:80'
);

my $tr_url = '';
foreach my $tr(@trackers) { $tr_url .= '&tr=' . uri_escape($tr); }

#$tr_url = join(',', @trackers);


# connect to db

# make threads
#push @threads, threads->create(\&main_thread);
push @threads, threads->create(\&main_thread);
for $y (1...$threads_limit) { 
	push @threads, threads->create(\&process_thread); 
}
foreach $thread(@threads) { $thread->join(); }

sub threads_wait {
	while($#tqueue > -1) {
#		print "Awaiting for threads(" . ($#tqueue+1) . ")...\n";
		sleep 5;
	}
}

sub main_thread {
	while(1) {
		$dbh 	= DBI->connect("dbi:mysql:retracker:localhost", "root", "");
		$dbh->do("set names 'utf8'");
		$qr = $dbh->prepare("select info_hash from tracker GROUP BY info_hash ORDER BY update_time DESC");
		$qr->execute();
		while(@row = $qr->fetchrow_array) {
			$hash = @row[0];
			next if(has_descr($hash) > 0);
			push @tqueue, $hash;
		}
		printf "Querying database(%d)...\n", ($#tqueue+1);
		$dbh->disconnect();
		threads_wait();
		sleep 30;
	}
}


sub main_thread_hash {
	while(1) {
		$dbh 	= DBI->connect("dbi:mysql:retracker:localhost", "root", "");
		for $n (1..1000) {
			$hash = rnd_hash();
			next if(has_descr($hash) > 0);
			push @tqueue, $hash;
		}
		printf "Querying database(%d)...\n", ($#tqueue+1);
		$dbh->disconnect();
		threads_wait();
		sleep 30;
	}
}


sub rnd_hash {
    my $ret = '';
    for my $v (1..32) {
	$i = sprintf("%x", rand(16));
	$ret .= $i;
    }
    return $ret;
}

sub process_thread {

	my $dbh;
	my $tid = threads->tid();

	while(1) {

		unless($hash = shift @tqueue) {
#			printf "Thread #%2d: Idle...\n", $tid;
			sleep 5;
			next;
		}

		$subdir = "$Bin/torrents/" . substr($hash, 0, 1) . "/" . substr($hash, 0, 2);

		make_path $subdir if(! -d $subdir);
		$myport = sprintf("%02d", $tid);
		$tfile = "$subdir/$hash.torrent";

		$source = 0;
		#$cmd0 = "wget -q -O \"${tfile}\" \"http://www.torrenthound.com/torrent/" . lc($hash) . "\"";
		#$cmd1 = "wget -q -O \"${tfile}.gz\" \"http://torrage.com/torrent/" . uc($hash) . ".torrent\" && gunzip ${tfile}.gz";
		#$cmd2 = "wget -q -O \"${tfile}.gz\" \"http://zoink.it/torrent/" . uc($hash) . ".torrent\" && gunzip ${tfile}.gz";
		$cmd3 = "aria2c --dir=$subdir --quiet  --enable-dht=true --bt-enable-lpd=true --bt-stop-timeout=30 --bt-metadata-only --bt-save-metadata \"magnet:?xt=urn:btih:${hash}${tr_url}\"";

#		open(LOG, ">>", "discovery.log");
#		printf LOG "Thread #%2d: Attracting $hash...\n", $tid;
#		printf "Thread #%2d: Attracting $hash...\n", $tid;
#		close(LOG);
#	        print "$tfile\n" ;

		if(! -e $tfile) {
#			print "torrenthound($hash)\n";
			#get_torrenthound($hash, $tfile);
			$source = 1;
		}

		if(! -e $tfile) {
#			print "torrage($hash)\n";
			#get_torrage($hash, $tfile);
			$source = 2;
		}

		if(! -e $tfile) {
#			print "zoink($hash)\n";
#			get_zoink($hash, $tfile);
			$source = 3;
		}

		if(! -e $tfile) {
			print "aria2c($hash)\n";
			system($cmd3);
			$source = 4;
		}

		sleep 1;
		if(-e $tfile) {
			local $/=undef;
			open(TOR, "<", $tfile); binmode(TOR); $torr_string = <TOR>; close(TOR);
			next if($torr_string eq '');
			
			my $torr = bdecode($torr_string);
			$tname = $torr->{info}->{name};

			$enc = detect($tname);
			$tname = encode("UTF-8", decode("windows-1251", $tname)) if($enc ne 'UTF-8');

			$files = '';
			$file_hash = ();

			if(! defined($torr->{info}->{files})) {
				$tsize = $torr->{info}->{length};
				$file_hash{$tname} = $torr->{info}->{length};
				push @{$file_hash}, {'name' => $tname, 'length' => $torr->{info}->{length}};
			} else {
				$tfiles = $torr->{info}->{files};
				$tsize = 0;
				foreach $i(@{$tfiles}) {
					$tsize += $i->{length}; 
					$tfname = join('/', @{$i->{path}});
					$enc = detect($tfname);
					if($enc ne 'UTF-8') {
						$tfname = encode("UTF-8", decode("windows-1251", $tfname));
					}
					push @{$file_hash}, {'name' => $tfname, 'length' => $i->{length}};
					$file_hash{$tfname} = $i->{length};
				}
			}

			if($tname ne '') { 
				$time = time();
				$dbh = DBI->connect("dbi:mysql:retracker:localhost", "root", "");
				$dbh->do("set names 'utf8'");
				$files = $dbh->quote($files);
				$tname_sql = $dbh->quote($tname);
				$filelist = $dbh->quote(bencode($file_hash));

				$test = $dbh->prepare("SELECT id FROM description WHERE info_hash = '$hash'");
				$test->execute();
				if($test->rows() == 1) {
					@row = $test->fetchrow_array();
					$id = @row[0];
					$dbh->do("	UPDATE 
									description 
								SET 
									info_text = ${tname_sql},
									size = '${tsize}',
									time = '${time}',
									filelist = ${filelist},
									flag = 1,
									censor = 0,
									source = '${source}'
								WHERE
									id = ${id}
							"); 
				} else {
					$dbh->do("	INSERT INTO 
									description 
									(info_hash, info_text, size, discovered, time, filelist, flag, source) 
								VALUES 
									('$hash', $tname_sql, '$tsize', '$time', '$time', $filelist, '1', '$source')
							"); 
				}
				$test->finish();
				open(LOG, ">>", "discovery.log");
				printf("%s thread #%2d: '$tname', '$tsize' (%d)\n", timez(), $tid, $source); 
				printf(LOG "%s thread #%2d: '$tname', '$tsize' (%d)\n", timez(), $tid, $source); 
				close(LOG);
				$dbh->disconnect();
			}
			#copy($tfile, '/usr/home/admin/transmission/watch/$hash.torrent');
			undef $torr;
		} else {
		}
	}
}

sub has_descr {
	my $hash = shift;

	my $subdir = "$Bin/torrents/" . substr($hash, 0, 1) . "/" . substr($hash, 0, 2);
	return 0 if(! -e "$subdir/$hash.torrent");

	my $o = $dbh->prepare("select info_text, size from description where info_hash = '$hash' limit 1");
	$o->execute();
	if($o->rows() == 1) {
		@arr = $o->fetchrow_array();
		if((@arr[0] ne '') && (@arr[1] > 0)) {
		    return 1;
		}
	}
	return 0;
}

sub timez {
	($sec, $min, $hour) = (localtime(time))[0..2];
	return sprintf("%02d:%02d:%02d", $hour, $min, $sec);
}

# getters
sub get_torrenthound {
	my $hash = shift;
	my $dst_file = shift;
	my $get_content = get("http://www.torrenthound.com/torrent/${hash}");
	return 0 if($get_content eq '');
	return 0 if($get_content eq 'The torrent you are requesting does not exist...');
	if((substr($get_content, 0, 11) eq 'd8:announce') or (substr($get_content, 0, ) eq 'd13:announce-list')) {
		# torrent seems to be OK
		open(TR, ">", $dst_file);
		print(TR $get_content);
		close(TR);
		return 1;
	}
	return 0;
}

sub get_torrage {
	my $hash = shift;
	my $dst_file = shift;
	my $get_content = get("http://torrage.com/torrent/" . uc($hash) . ".torrent");
	return 0 if($get_content eq '');

	if((substr($get_content, 0, 11) eq 'd8:announce') or (substr($get_content, 0, ) eq 'd13:announce-list')) {
		# torrent seems to be OK
		open(TR, ">", $dst_file);
		print(TR $get_content);
		close(TR);
		return 1;
	}
	return 0;
}

sub get_zoink {
	my $hash = shift;
	my $dst_file = shift;
	my $get_content = get("http://zoink.it/torrent/" . uc($hash) . ".torrent");
	return 0 if($get_content eq '');

	if((substr($get_content, 0, 11) eq 'd8:announce') or (substr($get_content, 0, ) eq 'd13:announce-list')) {
		# torrent seems to be OK
		open(TR, ">", $dst_file);
		print(TR $get_content);
		close(TR);
		return 1;
	}
	return 0;
}

