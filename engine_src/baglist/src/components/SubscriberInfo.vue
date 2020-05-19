<template>
  <div class="subscriber-info">

    <div v-if="mode=='confirm' " class="alert alert-success">
      ✔ Vielen Dank - Deine e-Mail Adresse wurde nun aktiviert.
    </div>

    <div v-for="(item,idx) in messages" :key="idx">
      <div>
        <div v-for="c in item.channels" :key="slug">
          <div v-if="item.event=='add' " class="alert alert-success">
            ✔ du wurdest zum Newsletter '{{c.name}}' hinzugefügt.
          </div>
          <div v-if="item.event=='remove'" class="alert alert-warning">
            ✖ du wurdest vom Newsletter '{{c.name}}' ausgetragen.
          </div>
        </div>
      </div>
    </div>

    <div class="person">
      <div class="person-icon">
        <i class='fa fa-vcard-o '></i>
      </div>
      <div class="person-data">
        <div class="person-name">
          {{person.vorname}} {{person.nachname}}
        </div>
        <div class="person-email">
          {{person.email}}
        </div>
        <div class="channels">
          <div class="channel-hl">
            du hast derzeit folgende Naturfreunde-Newsletter abonniert:
          </div>
          <div v-for="(g,index) in channelGroups" class="channelgroup" :key='index'>
            <div v-for="(i,index) in g.channels" v-if="isActiveChannel(i.slug)" class="channel" :key='index'>
              <i class='fa fa-envelope-o'></i>
              {{i.name}}
            </div>
          </div>
        </div>
        <a @click="$parent.gotoStep('edit')" class="btn btn-nf2">
          <i class="fa fa-chevron-right"></i> Einstellungen ändern</a>
      </div>

    </div>

  </div>
</template>

<script>
// const {create, env} = require('sanctuary');
// const checkTypes = process.env.NODE_ENV !== 'production';
// const S = create({checkTypes, env});
const R = require("ramda");

import Vue from "vue";
// some JS file
import store from "../store";

export default {
  name: "SubscriberInfo",
  props: {
    version: { Type: Number, required: true },
    mode: { Type: String, required: true },
    person: { Type: Object, required: true }
  },
  methods: {
    isActiveChannel: function(slug) {
      return R.contains(slug, this.person.subscribed_channels);
    }
  },
  computed: {
    channelGroups: function() {
      return this.$store.getters.groupedChannels;
    },
    messages: function() {
      return this.$store.getters.messages;
    }
  }
};
</script>

<style lang="scss">
@import "../styles/settings.scss";

.subscriber-info {
  .person {
    @include flexbox;

    .person-icon {
      padding-right: 0.5em;
      .fa {
        font-size: 2em;
      }
    }
    .person-data {
      border-left: 2px solid #ccc;
      padding-left: 1em;
      font-weight: bold;
      font-size: 1em;
      .person-email {
        margin-top: 1em;
        font-size: 1.3em;
        color: $c-green;
      }
      margin-bottom: 1em;
    }
    .channels {
      margin-top: 2em;
      margin-bottom: 2em;
      .channel-hl {
        font-weight: normal;
        margin: 1em 0;
      }
      .channel {
        margin-top: 0.4em;
        margin-bottom: 0.4em;
        line-height: 1.4;
        text-indent: -0.65em;
        margin-left: 1.4em;
      }
    }
  }
}
</style>
