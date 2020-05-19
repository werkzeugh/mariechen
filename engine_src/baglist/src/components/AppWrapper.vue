<template>
  <div id="baglistapp">
    <BagSearch></BagSearch>
    <ResultList></ResultList>
  </div>
</template>

<script>
import BagSearch from "./BagSearch";

import ResultList from "./ResultList";

export default {
  name: "AppWrapper",
  components: {
    BagSearch,
    ResultList
  }
};
</script>

<style lang="scss">
#baglistapp {
  color: black;
  width: 100%;
  // border: 1px solid green;
}
</style>
