# A simple make file to generate HTML/docbook
# 
#

PHP = php
MKDIR = mkdir -p
XSLHTML = /usr/share/xml/docbook/stylesheet/nwalsh/xhtml/chunk.xsl
XSLFO = /usr/share/xml/docbook/stylesheet/nwalsh/fo/docbook.xsl
OUTDIR = output
DEFAULTARGS = -x "*~"

all:: user internals developer css

css:
	cp book.css $(OUTDIR)/developer/	
	cp book.css $(OUTDIR)/internals/	
	cp book.css $(OUTDIR)/user/

book.css:

developer.pdf:
	$(MKDIR) $(OUTDIR)/developer/
	$(PHP) MakeDocbook.php -o $(OUTDIR)/developer/$@ $(DEFAULTARGS) -p $(XSLFO) Developer
	
developer:
	$(MKDIR) $(OUTDIR)/developer/
	$(PHP) MakeDocbook.php -o $(OUTDIR)/developer/ $(DEFAULTARGS) -p $(XSLHTML) Developer

internals:
	$(MKDIR) $(OUTDIR)/internals/
	$(PHP) MakeDocbook.php -o $(OUTDIR)/internals/ $(DEFAULTARGS) -p $(XSLHTML) Internals

user:
	$(MKDIR) $(OUTDIR)/user/
	$(PHP) MakeDocbook.php -o $(OUTDIR)/user/ $(DEFAULTARGS) -p $(XSLHTML) Users

clean:
	rm -rf $(OUTDIR)/

.PHONEY: clean user internals developer css
