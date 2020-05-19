<template>
  <div class="subscriber-form">
    <div class="cancelbtn">
      <a @click="$parent.gotoStep('info')" class="btn btn-default" v-if="mode!=='create'">
        <i class="fa fa-chevron-left"></i> zurück</a>
    </div>

    <div class="row">
      <form-group class="span-6 alpha" label="Vorname" :validator="$v.localPerson.vorname">
        <input type="text" class="form-control" v-model.trim="localPerson.vorname" name="Vorname" @input="$v.localPerson.vorname.$touch()">
      </form-group>

      <form-group class="span-6 omega" label="Nachname" :validator="$v.localPerson.nachname">
        <input type="text" class="form-control" v-model.trim="localPerson.nachname" name="Nachname" @input="$v.localPerson.nachname.$touch()">
      </form-group>
    </div>

    <div class="row">
      <form-group class="span-12 alpha" label="E-Mail Adresse" :validator="$v.localPerson.email">
        <input v-if="emailIsEditable" class="form-control" type="text" v-model.trim="localPerson.email" name="Email" @input="$v.localPerson.email.$touch()">
        <div v-else class="email-solo">{{localPerson.email}}</div>
        <div>&nbsp;</div>
      </form-group>

    </div>

    <div class="row">
      <form-group class="form-group span-12" label="Ich möchte folgende Newsletter erhalten:" :validator="$v.localPerson.subscribed_channels">
        <div class="channels">
          <div v-for="(g,index) in channelGroups" class="channelgroup" :key='index'>
            <div v-for="(i,index2) in g.channels" :class="{active:isActiveChannel(i.slug)}" @click="toggleChannel(i.slug)" class="channel" :key='index2'>
              <i class='fa  fa-square-o is-off'></i>
              <i class='fa  fa-check-square is-on'></i>
              {{i.name}}
            </div>
          </div>
        </div>

      </form-group>
    </div>

    <div class="row form-footer">
      <div class="span-12">
        <div v-if="saving" class="saving-div">
          speichern...
          <i class='fa fa-spin fa-spinner'></i>
        </div>
        <div v-else>

          <button class="btn btn-nf2 btn-lg" :class="$v.localPerson.$invalid?'btn-default':'btn-nf2'" type="button" @click="savePerson()">
            <i class="fa fa-chevron-right"></i> {{buttonText}}</button>
        </div>
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
import vuelidate from "vuelidate";
import { required, email } from "vuelidate/lib/validators";
import { withParams } from "vuelidate/lib";
import vuelidateErrorExtractor from "vuelidate-error-extractor";
import axios from "axios";

// some JS file
import store from "../store";

import customFormGroup from "./FormGroup.vue";

Vue.use(vuelidate);
Vue.use(vuelidateErrorExtractor, {
  template: customFormGroup,
  messages: {
    required: "Dieses Feld ist ein Pflichtfeld",
    email: "Bitte eine gültige E-Mail Adresse eintragen"
  }
});

export default {
  name: "SubscriberForm",
  props: {
    version: { Type: Number, required: true },
    mode: { Type: String, required: true },
    buttonText: { Type: String, required: true },
    saving: { Type: Boolean, required: true }
  },
  data: function() {
    return {
      localPerson: null,
      asyncTemplate: "{{ item.label }}"
    };
  },
  computed: {
    emailIsEditable: function() {
      return this.mode == "create";
    },
    nameIsOptional: function() {
      return true;
      // return this.mode !== "reconfirm";
    },
    channelGroups: function() {
      return this.$store.getters.groupedChannels;
    }
  },
  methods: {
    fetchPerson: function() {
      let p = this.$store.getters.person;
      console.log("#fetchPerson", p);
      p = R.clone(p);
      return { ...p };
      1;
    },
    savePerson: function() {
      if (this.$v.localPerson.$invalid) {
        this.$v.localPerson.$touch();
        alert("Bitte fülle alle rot markierten Felder aus.");
      } else {
        this.$store.commit("setPerson", this.localPerson);
        this.$parent.submitForm();
      }
    },
    isActiveChannel: function(id) {
      return R.contains(id, this.localPerson.subscribed_channels);
    },
    toggleChannel: function(slug) {
      if (R.find(R.equals(slug))(this.localPerson.subscribed_channels)) {
        this.localPerson.subscribed_channels = R.reject(
          R.equals(slug),
          this.localPerson.subscribed_channels
        );
      } else {
        this.localPerson.subscribed_channels.push(slug);
      }
      this.$v.localPerson.subscribed_channels.$touch();
    }
  },
  validations: function() {
    let validations = {
      vorname: {},
      nachname: {},
      subscribed_channels: {},
      email: { required, email }
    };

    if (!this.emailIsEditable) {
      validations = R.omit(["Email"], validations);
    }
    if (this.nameIsOptional) {
      validations = R.omit(["Vorname", "Nachname"], validations);
    }
    return { localPerson: validations };
  },
  watch: {
    version: function() {
      this.localPerson = this.fetchPerson();
    }
  },
  created: function() {
    this.localPerson = this.fetchPerson();
  }
};
</script>

<style lang="scss">
@import "../styles/settings.scss";

.subscriber-form {
  .cancelbtn {
    text-align: right;
    margin-bottom: 1em;
  }
  .person-details {
    padding: 0 1em;
    > div {
      @include flexbox;
      > * {
        margin-top: 0.3em;
        margin-bottom: 0.3em;
      }
      > span {
        @include flex(1, 1, 10em);
        color: $c-green;
      }
      > div {
        @include flex(1, 1, 70%);
        // border:1px solid red;
      }
    }
  }

  .has-error .v-select .dropdown-toggle {
    border-color: #a94442;
  }
  .has-success .v-select .dropdown-toggle {
    border-color: #3c763d;
  }

  .saving-div {
    padding: 1em;
    text-align: center;
    font-weight: bold;
    font-size: 1.5em;
  }

  .form-header {
    background: #efefef;
    border-top: 2px solid $c-green;
    .form-headline {
      padding: 0.5em;
      font-size: 1.5em;
      font-weight: bold;
    }
    // margin-bottom: 1em;
  }
  .form-footer {
    background: #efefef;
    text-align: right;
    border-bottom: 2px solid $c-green;
    padding: 1em;

    margin-bottom: 1em;
  }
  .form-open,
  .person-details {
    padding-top: 1em;
    padding-bottom: 1em;
  }

  .dimmed {
    opacity: 0.6;
  }

  .email-solo {
    padding: 1em;
    font-weight: bold;
    text-align: center;
    color: $c-green;
    font-size: 1.4em;
  }
  .channelgroup {
    padding: 1em;
    .grouptitle {
      font-size: 1.2em;
      padding-bottom: 1em;
    }

    .channel {
      color: #888;
      font-weight: bold;
      padding: 0.3em 0.5em;
      font-size: 1.1em;
      // text-indent: -1.5em;
      margin-left: 1em;
      // border: 1px solid red;
      position: relative;
      .is-on {
        display: none;
      }
      cursor: pointer;
      .fa {
        position: absolute;
        left: -20px;
        font-size: 1.4em;
        vertical-align: middle;
        width: 1em;
      }
      &:hover {
        background: #eee;
      }
      &.active {
        color: $c-green;

        .is-off {
          display: none;
        }
        .is-on {
          display: inline-block;
        }
      }
    }
  }
}
</style>
