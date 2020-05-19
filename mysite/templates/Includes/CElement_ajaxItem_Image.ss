<% if editMode %>
 <input type='hidden' name='fdata[PictureID]' class='MwFileField' value='$PictureID'> 
 <div class='space'>
   <label>Image-Copyright:</label>
   <input type='text' name='fdata[PictureCopyright]'  id='input_PictureCopyright' value='$PictureCopyright'> 
   <label>Image-Text:</label>
   <input type='text' name='fdata[PictureText]'  id='input_PictureText' value='$PictureText'> 
 </div>
 <% else %>
 $Picture.Image.CMSThumbnail
<% end_if %>
