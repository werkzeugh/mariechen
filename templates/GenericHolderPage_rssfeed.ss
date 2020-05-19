<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">

  <title>$FeedTitle</title> 
  <link href="http://example.org/"/>
  <updated>2003-12-13T18:30:02Z</updated>
  <author> 
    <name>Wirtschaftsagentur Wien</name>
  </author> 
  <id>urn:uuid:$Link</id>

<% loop  AtomItems %>
  <entry>
    <title>$AtomTitle</title>
    <link href="$AbsoluteLink"></link>
    <id>urn:uuid:$ClassName-$ID</id>
    <updated>$AtomDate</updated>
    <summary>$RssField(ShortText)</summary>
  </entry>
  
<% end_loop %>


</feed>
