<template>
  <div class='newsletter-form'>
    <template v-if="step=='step0'">
      <i class='fa fa-spin fa-spinner'></i>
    </template>
    <template v-if="step=='edit'">
      <SubscriberForm :version="version" :mode="mode" :saving="saving" :button-text="buttonText"></SubscriberForm>
    </template>
    <template v-if="step=='info'">
      <SubscriberInfo :version="version" :mode="mode" :person="currentPerson"></SubscriberInfo>

    </template>
    <template v-if="step=='data_saved'">

      <i class='fa fa-check fa-3x green fa-fw'></i> deine Newsletter-Einstellungen wurden gespeichert.
      <div>&nbsp;</div>
      <div>&nbsp;</div>
      <a :href="'https://newsletter.naturfreunde.at/settings/?c='+currentPerson.code" class="btn btn-nf2">
        <i class="fa fa-chevron-right"></i> weiter</a>
    </template>
    <template v-if="step=='email_sent'">
      <div class="email-sent-info">
        <div>
          <h2>Schritt 2: Bitte prüfe dein E-Mail Postfach !</h2>
          Wir haben dir soeben ein E-Mail an
          <strong>{{currentPerson.email}}</strong> gesendet.
          <div>&nbsp;</div>
          <strong>Um deine Anmeldung zu bestätigen, musst du unbedingt noch den Bestätigungslink in dieser E-Mail anklicken !</strong>
          <div>&nbsp;</div>
          Solltest du kein Mail erhalten haben, so prüfe bitte deinen SPAM-Ordner:
          <div>&nbsp;</div>
          Der Absender der E-Mail lautet: <br>
          <em>noreply@newsletter.naturfreunde.at</em>
          <div>&nbsp;</div> und der Betreff: <br>
          <em>{{subject}}</em>
          <div>&nbsp;</div>

          <button v-if="false" class="btn btn-default" :disabled="!anmeldungKomplett || saving" type="button" @click="sendAnmeldung(true)">
            <i class="fa fa-chevron-right"></i> E-Mail erneut senden</button>

          <span v-if="saving" class="saving-div">
            senden...
            <i class='fa fa-spin fa-spinner'></i>
          </span>
          <div>&nbsp;</div>
          <transition name="fade">
            <div v-if='sendagainMessage' class="alert alert-info">{{sendagainMessage}}</div>
          </transition>
        </div>
      </div>
    </template>

  </div>
</template>

<script>
import SubscriberForm from "../components/SubscriberForm";
import SubscriberInfo from "../components/SubscriberInfo";
const R = require("ramda");

import Vue from "vue";

const hasValue = R.complement(R.either(R.isNil, R.isEmpty));

export default {
  name: "NewsletterForm",
  data: function() {
    return {
      saving: false,
      sendagainMessage: "",
      sendCount: 0
    };
  },
  components: {
    SubscriberForm,
    SubscriberInfo
  },
  computed: {
    version: function() {
      return this.$store.getters.version;
    },
    buttonText: function() {
      let txt = "";
      switch (this.mode) {
        case "create":
          txt = "Weiter";
          break;
        default:
          txt = "Änderungen Speichern";
          break;
      }
      return txt;
    },
    mode: function() {
      return this.$store.getters.mode;
    },
    currentPerson: function() {
      return this.$store.getters.person;
    },
    subject: function() {
      return this.$store.getters.subject;
    },
    step: function() {
      return this.$store.getters.currentStep;
    }
  },
  methods: {
    gotoStep: function(step) {
      this.$store.commit("setStep", step);
    },
    submitForm: function() {
      this.saving = true;

      this.$store
        .dispatch("savePerson", { person: this.currentPerson, mode: this.mode })
        .then(
          response => {
            console.log("#response", response);
            this.saving = false;
            if (this.mode == "create") {
              this.$store.commit("setSubject", response.data.payload.subject);
              this.$store.commit("setStep", "email_sent");
            } else {
              this.$store.commit("setStep", "data_saved");
            }
          },
          () => {
            alert(
              "Es ist ein Fehler aufgetreten. Die Daten konnten leider nicht gespeichert werden."
            );
            this.saving = false;
          }
        );
    },
    fetchPersonIfNeeded: function() {
      if (
        this.$store.getters.extconf.code &&
        this.$store.getters.extconf.mode !== "create"
      ) {
        this.$store
          .dispatch("fetchPerson", this.$store.getters.extconf.code)
          .then(() => {
            this.$store.dispatch("fetchChannels").then(() => {
              this.$store.commit("setStep", "info");
            });
          });
      } else {
        this.$store.dispatch("fetchChannels").then(() => {
          this.$store.commit("setStep", "edit");
        });
      }
    }
  },
  validations: function() {
    let validations = {
      currentProfile: {}
    };
    return validations;
  },
  created: function() {
    this.fetchPersonIfNeeded();
  }
};
</script>

<style lang="scss">
@import "../styles/settings.scss";

.newsletter-form {
  > .form-header {
    text-align: right;
  }
  .nextbutton-holder {
    margin-top: 4em;
    text-align: right;
  }
  .price {
    font-size: 1.2em;
    font-weight: bold;
    display: block;
    color: $c-green;
    margin-bottom: 0.5em;
  }
  .desc {
    color: #999;
  }
  .green {
    color: $c-green;
  }
  .email-sent-info {
    em {
      font-style: normal;
      color: $c-green;
      font-weight: bold;
      margin-top: 0.2em;
      display: block;
    }
    @include flexbox;
    @include align-items(center);
    > div {
      @include flex(1, 1, auto);
      > .fa {
        color: $c-green;
        font-size: 6em;
        margin-right: 0.2em;
      }
    }
  }
  .saving-div {
    color: $c-green;
  }
  .payment-row {
    margin-top: 3em;
  }

  .boxholder {
    @include flexbox;
    max-width: 500px;
    margin: 2em auto;
    &.disabled {
      .m-box {
        cursor: not-allowed;
      }
      .m-box.not-active,
      .m-box.not-active:hover {
        opacity: 0.3;
        border-color: #999;
        .hl,
        .fa,
        .txt {
          color: #999;
        }
        background: inherit;
      }
    }
    .m-between {
      @include flex(1, 0, 1em);
    }
    .m-box {
      cursor: pointer;
      @include flex(0, 1, 50%);
      border: 2px solid $c-green;
      border-radius: 8px;
      padding: 1em;
      min-height: 200px;
      text-align: center;
      &.active,
      &:hover {
        background: $c-green;
        color: #fff;
      }
      &.active {
        .hl,
        .marker,
        .txt {
          color: #fff;
        }
      }
      &:hover {
        .hl,
        .marker,
        .txt {
          color: #fff;
        }
      }
      .hl {
        font-size: 1.2em;
        font-weight: 700;
        color: $c-green;
        margin-bottom: 1.5em;
      }
      .txt {
        color: #999;
      }
      .marker {
        margin: 1em;
        color: $c-green;
      }
      .fa {
        font-size: 3em;
      }
    }
  }
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 1s;
  }
  .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
    opacity: 0;
  }
}
</style>
