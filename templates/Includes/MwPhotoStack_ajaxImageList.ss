<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
  <head>
    <title></title>
  </head>
  <body>
    <div class='imagelist_header'>
      Number of Images <strong>$record.photostack_Images.Count</strong> | Memory Usage <strong>$record.photostack_UsageInMB</strong> | Sort: filename ascending
    </div>
    <ul class='imagelist'>
      <% loop record.photostack_Images %>
      <li filename='$Name'>
        <div title="$CombinedTitle" style="width:{$CMSThumbnail.getWidth}px;height:{$CMSThumbnail.getHeight}px;background-image:url('$CMSThumbnail.Link')" class="thumbnail">
          <div class="control">
            <ul>
              <!-- <li><a href='/BE/Album/ajaxSetFrontImage/$Album.ID?file=$Name' class='setfrontimage button' title='use this image as gallery-start-image'><span></span>set as start-image</a></li>
          <li><a href='/BE/Album/iframeEditImage/$Album.ID?file=$Name' class='iframepopup edit button' title='set Title & Copyright for this image'><span></span>edit</a></li> -->
              <li>
                <a href='$Top.CurrentURL?ajaxdelete=1&amp;file=$NameEscaped' class='delete button' title='remove this image'>delete</a>
              </li>
            </ul>
          </div>
        </div>
      </li><% end_loop %>
    </ul>
     <div class='space'>
        <a href='#' class='button delete alldelete confirm'><span></span>Delete all images</a>
      </div>

    <script type="text/javascript" charset="utf-8">
$(document).ready(function() {
    //$('li[filename=$record.myFrontImage]').addClass('frontimage');
    });
    </script>
  </body>
</html>
