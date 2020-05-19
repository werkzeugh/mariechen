<template>

    <b-notification :type="data.class" :closable="true" @close="$parent.close()">
        {{data.text}}
    </b-notification>

</template>
<script>
export default {
  props: {
    data: { type: Object, required: true }
  }
};
</script>
<style lang="scss" >
$size: 40px;
.modal button.delete {
  height: $size;
  max-height: $size;
  max-width: $size;
  min-height: $size;
  min-width: $size;
  width: $size;
}
</style>



