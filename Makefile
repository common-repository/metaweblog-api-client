VERSION=0.2

all: mwac-$(VERSION).zip

mwac-$(VERSION).zip: README INSTALL mwac-data.sql ixr.bloggerclient.php mwac.php
	mkdir mwac
	cp $^ mwac
	zip $@ mwac/*
	rm -rf mwac
