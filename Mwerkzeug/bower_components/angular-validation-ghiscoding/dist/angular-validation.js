//by mwuits, concated for debugging purposes:

/**
 * Angular-Validation Directive (ghiscoding)
 * https://github.com/ghiscoding/angular-validation
 *
 * @author: Ghislain B.
 * @started: 2014-02-04
 *
 * @desc: If a field becomes invalid, the text inside the error <span> or <div> will show up because the error string gets filled
 * Though when the field becomes valid then the error message becomes an empty string,
 * it will be transparent to the user even though the <span> still exist but becomes invisible since the text is empty.
 *
 */
 angular
  .module('ghiscoding.validation', ['pascalprecht.translate'])
  .directive('validation', ['$timeout', 'validationCommon', function($timeout, validationCommon) {
    return {
      restrict: "A",
      require: "ngModel",
      link: function(scope, elm, attrs, ctrl) {
        // create an object of the common validation
        var commonObj = new validationCommon(scope, elm, attrs, ctrl);
        var timer;
        var blurHandler;
        var isValidationCancelled = false;

        // construct the functions, it's just to make the code cleaner and put the functions at bottom
        var construct = {
          attemptToValidate: attemptToValidate,
          cancelValidation : cancelValidation
        };

        // attach the attemptToValidate function to the element
        // wrap the calls into a $evalAsync so that if falls at the end of the $digest, because other tool like Bootstrap UI might interfere with our validation
        scope.$evalAsync(function() {
          ctrl.$formatters.unshift(attemptToValidate);
          ctrl.$parsers.unshift(attemptToValidate);
        });

        // watch the `disabled` attribute for changes
        // if it become disabled then skip validation else it becomes enable then we need to revalidate it
        attrs.$observe("disabled", function(disabled) {
          if (disabled) {
            // Turn off validation when element is disabled & remove it from validation summary
            cancelValidation();
            commonObj.removeFromValidationSummary(attrs.name);
          } else {
            // revalidate & re-attach the onBlur event
            revalidateAndAttachOnBlur();
          }
        });

        // if DOM element gets destroyed, we need to cancel validation, unbind onBlur & remove it from $validationSummary
        elm.on('$destroy', function() {
          cancelAndUnbindValidation();
        });

        // watch for a validation becoming empty, if that is the case, unbind everything from it
        scope.$watch(function() {
          return elm.attr('validation');
        }, function(validation) {
          if(typeof validation === "undefined" || validation === '') {
            // if validation gets empty, we need to cancel validation, unbind onBlur & remove it from $validationSummary
            cancelAndUnbindValidation();
          }else {
            // If validation attribute gets filled/re-filled (could be by interpolation)
            //  we need to redefine the validation so that we can grab the new "validation" element attribute
            // and finally revalidate & re-attach the onBlur event
            commonObj.defineValidation();
            revalidateAndAttachOnBlur();
          }
        });

        // onBlur make validation without waiting
        elm.bind('blur', blurHandler);

        function blurHandler(event) {
          // get the form element custom object and use it after
          var formElmObj = commonObj.getFormElementByName(ctrl.$name);

          if (!formElmObj.isValidationCancelled) {
            // validate without delay
            attemptToValidate(event.target.value, 10);
          }else {
            ctrl.$setValidity('validation', true);
          }
        }

        //----
        // Private functions declaration
        //----------------------------------

        /** Validator function to attach to the element, this will get call whenever the input field is updated
         *  and is also customizable through the (typing-limit) for which inactivity this.timer will trigger validation.
         * @param string value: value of the input field
         */
        function attemptToValidate(value, typingLimit) {
          // get the waiting delay time if passed as argument or get it from common Object
          var waitingLimit = (typeof typingLimit !== "undefined") ? typingLimit : commonObj.typingLimit;

          // get the form element custom object and use it after
          var formElmObj = commonObj.getFormElementByName(ctrl.$name);

          // pre-validate without any events just to pre-fill our validationSummary with all field errors
          // passing false as 2nd argument for not showing any errors on screen
          commonObj.validate(value, false);

          // if field is not required and his value is empty, cancel validation and exit out
          if(!commonObj.isFieldRequired() && (value === "" || value === null || typeof value === "undefined")) {
            cancelValidation();
            return value;
          }else if(!!formElmObj) {
            formElmObj.isValidationCancelled = false;
          }

          // invalidate field before doing any validation
          if(!!value || commonObj.isFieldRequired()) {
            ctrl.$setValidity('validation', false);
          }

          // if a field holds invalid characters which are not numbers inside an `input type="number"`, then it's automatically invalid
          // we will still call the `.validate()` function so that it shows also the possible other error messages
          if((value === "" || typeof value === "undefined") && elm.prop('type').toUpperCase() === "NUMBER") {
            $timeout.cancel(timer);
            ctrl.$setValidity('validation', commonObj.validate(value, true));
            return value;
          }

          // select(options) will be validated on the spot
          if(elm.prop('tagName').toUpperCase() === "SELECT") {
            ctrl.$setValidity('validation', commonObj.validate(value, true));
            return value;
          }

          // onKeyDown event is the default of Angular, no need to even bind it, it will fall under here anyway
          // in case the field is already pre-filled, we need to validate it without looking at the event binding
          if(typeof value !== "undefined") {
            // Make the validation only after the user has stopped activity on a field
            // everytime a new character is typed, it will cancel/restart the timer & we'll erase any error mmsg
            commonObj.updateErrorMsg('');
            $timeout.cancel(timer);
            timer = $timeout(function() {
              scope.$evalAsync(ctrl.$setValidity('validation', commonObj.validate(value, true) ));
            }, waitingLimit);
          }

          return value;
        } // attemptToValidate()

        /** Cancel the validation, unbind onBlur and remove from $validationSummary */
        function cancelAndUnbindValidation() {
          // unbind everything and cancel the validation
          ctrl.$formatters.shift();
          ctrl.$parsers.shift();
          cancelValidation();
          commonObj.removeFromValidationSummary(attrs.name);
        }

        /** Cancel current validation test and blank any leftover error message */
        function cancelValidation() {
          // get the form element custom object and use it after
          var formElmObj = commonObj.getFormElementByName(ctrl.$name);
          if(!!formElmObj) {
            formElmObj.isValidationCancelled = true;
          }
          $timeout.cancel(timer);
          commonObj.updateErrorMsg('');
          ctrl.$setValidity('validation', true);

          // unbind onBlur handler (if found) so that it does not fail on a non-required element that is now dirty & empty
          if(typeof blurHandler === "function") {
            elm.unbind('blur', blurHandler);
          }
        }

        /** Re-evaluate the element and revalidate it, also re-attach the onBlur event on the element */
        function revalidateAndAttachOnBlur() {
          // Revalidate the input when enabled (without displaying the error)
          var value = ctrl.$viewValue || '';
          ctrl.$setValidity('validation', commonObj.validate(value, false));

          // get the form element custom object and use it after
          var formElmObj = commonObj.getFormElementByName(ctrl.$name);
          if(!!formElmObj) {
            formElmObj.isValidationCancelled = false; // make sure it's renable validation as well
          }

          // re-attach the onBlur handler
          elm.bind('blur', blurHandler);
        }

      } // link()
    }; // return;
  }]); // directive


/**
 * angular-validation-common (ghiscoding)
 * https://github.com/ghiscoding/angular-validation
 *
 * @author: Ghislain B.
 * @desc: angular-validation common functions used by both the Directive & Service
 *
 */
angular
  .module('ghiscoding.validation')
  .factory('validationCommon', ['$rootScope', '$translate', 'validationRules', function ($rootScope, $translate, validationRules) {
    // global variables of our object (start with _var), these variables are shared between the Directive & Service
    var _bFieldRequired = false;            // by default we'll consider our field not required, if validation attribute calls it, then we'll start validating
    var _INACTIVITY_LIMIT = 1000;           // constant of maximum user inactivity time limit, this is the default cosntant but can be variable through typingLimit variable
    var _formElements = [];                 // Array of all Form Elements, this is not a DOM Elements, these are custom objects defined as { fieldName, elm,  attrs, ctrl, isValid, message }
    var _globalOptions = {                  // Angular-Validation global options, could be define by scope.$validationOptions or by validationService.setGlobalOptions()
      resetGlobalOptionsOnRouteChange: true // do we want to reset the Global Options on a route change? True by default
    };
    var _remotePromises = [];               // keep track of promises called and running when using the Remote validator
    var _validationSummary = [];            // Array Validation Error Summary

    // watch on route change, then reset some global variables, so that we don't carry over other controller/view validations
    $rootScope.$on("$routeChangeStart", function (event, next, current) {
      if (_globalOptions.resetGlobalOptionsOnRouteChange) {
        _globalOptions = {
          displayOnlyLastErrorMsg: false,   // reset the option of displaying only the last error message
          preValidateFormElements: false,   // reset the option of pre-validate all form elements, false by default
          isolatedScope: null,              // reset used scope on route change
          scope: null,                      // reset used scope on route change
          resetGlobalOptionsOnRouteChange: true
        };
        _formElements = [];                 // array containing all form elements, valid or invalid
        _validationSummary = [];            // array containing the list of invalid fields inside a validationSummary
      }
    });

    // service constructor
    var validationCommon = function (scope, elm, attrs, ctrl) {
      this.bFieldRequired = false; // by default we'll consider our field as not required, if validation attribute calls it, then we'll start validating
      this.validators = [];
      this.typingLimit = _INACTIVITY_LIMIT;
      this.scope = scope;
      this.elm = elm;
      this.ctrl = ctrl;
      this.validatorAttrs = attrs;

      if(!!scope && !!scope.$validationOptions) {
        _globalOptions = scope.$validationOptions; // save the global options
      }

      // user could pass his own scope, useful in a case of an isolate scope
      if (!!scope && (!!_globalOptions.isolatedScope || !!_globalOptions.scope)) {
        this.scope = _globalOptions.isolatedScope || _globalOptions.scope;  // overwrite original scope (isolatedScope/scope are equivalent arguments)
        _globalOptions = mergeObjects(scope.$validationOptions, _globalOptions);                              // reuse the validationOption from original scope
      }

      // if the resetGlobalOptionsOnRouteChange doesn't exist, make sure to set it to True by default
      if(typeof _globalOptions.resetGlobalOptionsOnRouteChange === "undefined") {
        _globalOptions.resetGlobalOptionsOnRouteChange = true;
      }

      // only the angular-validation Directive can possibly reach this condition with all properties filled
      // on the other hand the angular-validation Service will `initialize()` function to initialize the same set of variables
      if (!!this.elm && !!this.validatorAttrs && !!this.ctrl && !!this.scope) {
        addToFormElementObjectList(this.elm, this.validatorAttrs, this.ctrl, this.scope);
        this.defineValidation();
      }
    };

    // list of available published public functions of this object
    validationCommon.prototype.arrayFindObject = arrayFindObject;                                   // search an object inside an array of objects
    validationCommon.prototype.defineValidation = defineValidation;                                 // define our validation object
    validationCommon.prototype.getFormElementByName = getFormElementByName;                         // get the form element custom object by it's name
    validationCommon.prototype.getFormElements = getFormElements;                                   // get the array of form elements (custom objects)
    validationCommon.prototype.getGlobalOptions = getGlobalOptions;                                 // get the global options used by all validators (usually called by the validationService)
    validationCommon.prototype.isFieldRequired = isFieldRequired;                                   // return boolean knowing if the current field is required
    validationCommon.prototype.initialize = initialize;                                             // initialize current object with passed arguments
    validationCommon.prototype.mergeObjects = mergeObjects;                                         // merge 2 javascript objects, Overwrites obj1's values with obj2's (basically Object2 as higher priority over Object1)
    validationCommon.prototype.removeFromValidationSummary = removeFromValidationSummary;           // remove an element from the $validationSummary
    validationCommon.prototype.removeFromFormElementObjectList = removeFromFormElementObjectList;   // remove named items from formElements list
    validationCommon.prototype.setDisplayOnlyLastErrorMsg = setDisplayOnlyLastErrorMsg;             // setter on the behaviour of displaying only the last error message
    validationCommon.prototype.setGlobalOptions = setGlobalOptions;                                 // set global options used by all validators (usually called by the validationService)
    validationCommon.prototype.updateErrorMsg = updateErrorMsg;                                     // update on screen an error message below current form element
    validationCommon.prototype.validate = validate;                                                 // validate current element

    // override some default String functions
    String.prototype.trim = stringPrototypeTrim;
    String.prototype.format = stringPrototypeFormat;
    String.format = stringFormat;

    // return the service object
    return validationCommon;

    //----
    // Public functions declaration
    //----------------------------------

    /** Define our validation object
     * @return object self
     */
    function defineValidation() {
      var self = this;
      var customUserRegEx = {};
      self.validators = [];        // reset the global validators

      // debounce (alias of typingLimit) timeout after user stop typing and validation comes in play
      self.typingLimit = _INACTIVITY_LIMIT;
      if (self.validatorAttrs.hasOwnProperty('debounce')) {
        self.typingLimit = parseInt(self.validatorAttrs.debounce, 10);
      } else if (self.validatorAttrs.hasOwnProperty('typingLimit')) {
        self.typingLimit = parseInt(self.validatorAttrs.typingLimit, 10);
      } else if (!!_globalOptions && _globalOptions.hasOwnProperty('debounce')) {
        self.typingLimit = parseInt(_globalOptions.debounce, 10);
      }

      // get the rules(or validation), inside directive it's named (validation), inside service(rules)
      var rules = self.validatorAttrs.rules || self.validatorAttrs.validation;

      // We first need to see if the validation holds a custom user regex, if it does then deal with it first
      // So why deal with it separately? Because a Regex might hold pipe '|' and so we don't want to mix it with our regular validation pipe
      if(rules.indexOf("pattern=/") >= 0) {
        var matches = rules.match(/pattern=(\/.*\/[igm]*)(:alt=(.*))?/);
        if (!matches || matches.length < 3) {
          throw 'Regex validator within the validation needs to be define with an opening "/" and a closing "/", please review your validator.';
        }
        var pattern = matches[1];
        var altMsg = (!!matches[2]) ? matches[2].replace(/\|(.*)/, '') : '';

        // convert the string into a real RegExp pattern
        var match = pattern.match(new RegExp('^/(.*?)/([gimy]*)$'));
        var regex = new RegExp(match[1], match[2]);

        customUserRegEx = {
          altMsg: altMsg,
          message: altMsg.replace(/:alt=/, ''),
          pattern: regex
        };

        // rewrite the rules so that it doesn't contain any regular expression
        // we simply remove the pattern so that it won't break the Angular-Validation since it also use the pipe |
        rules = rules.replace('pattern=' + pattern, 'pattern');
      }
      // DEPRECATED, in prior version of 1.3.34 and less, the way of writing a regular expression was through regex:/.../:regex
      // this is no longer supported but is still part of the code so that it won't break for anyone using previous way of validating
      // Return string will have the complete regex pattern removed but we will keep ':regex' so that we can still loop over it
      else if (rules.indexOf("regex:") >= 0) {
        var matches = rules.match("regex:(.*?):regex");
        if (matches.length < 2) {
          throw 'Regex validator within the validation needs to be define with an opening "regex:" and a closing ":regex", please review your validator.';
        }
        var regAttrs = matches[1].split(':=');
        customUserRegEx = {
          message: regAttrs[0],
          pattern: regAttrs[1]
        };

        // rewrite the rules so that it doesn't contain the regex: ... :regex ending
        // we simply remove it so that it won't break if there's a pipe | inside the actual regex
        rules = rules.replace(matches[0], 'regex:');
      }

      // at this point it's safe to split with pipe (since regex was previously stripped out)
      var validations = rules.split('|');

      if (validations) {
        self.bFieldRequired = (rules.indexOf("required") >= 0) ? true : false;

        // loop through all validators of the element
        for (var i = 0, ln = validations.length; i < ln; i++) {
          // params split will be:: [0]=rule, [1]=ruleExtraParams OR altText, [2] altText
          var params = validations[i].split(':');

          // check if user provided an alternate text to his validator (validator:alt=Alternate Text)
          var hasAltText = validations[i].indexOf("alt=") >= 0 ? true : false;

          self.validators[i] = validationRules.getElementValidators({
            altText: hasAltText === true ? (params.length === 2 ? params[1] : params[2]) : '',
            customRegEx: customUserRegEx,
            rule: params[0],
            ruleParams: (hasAltText && params.length === 2) ? null : params[1]
          });
        }
      }
      return self;
    } // defineValidation()

    /** Return a Form element object by it's name
     * @return array object elements
     */
    function getFormElementByName(elmName) {
      return arrayFindObject(_formElements, 'fieldName', elmName);
    }

    /** Return all Form elements
     * @return array object elements
     */
    function getFormElements(formName) {
      if(!!formName) {
        return arrayFindObjects(_formElements, 'formName', formName);
      }
      return _formElements;
    }

    /** Get global options used by all validators
     * @param object attrs: global options
     * @return object self
     */
    function getGlobalOptions() {
      return _globalOptions;
    }

    /** Initialize the common object
     * @param object scope
     * @param object elm
     * @param object attrs
     * @param object ctrl
     */
    function initialize(scope, elm, attrs, ctrl) {
      this.scope = scope;
      this.elm = elm;
      this.ctrl = ctrl;
      this.validatorAttrs = attrs;

      addToFormElementObjectList(elm, attrs, ctrl, scope);
      this.defineValidation();
    }

    /** @return isFieldRequired */
    function isFieldRequired() {
      var self = this;
      return self.bFieldRequired;
    }

    /**
     * Overwrites obj1's values with obj2's and adds obj2's if non existent in obj1
     * When both object have the same property, the Object2 will higher priority over Object1 (basically that property will be overwritten inside Object1)
     * @param obj1
     * @param obj2
     * @return obj3 a new object based on obj1 and obj2
     */
    function mergeObjects(obj1, obj2) {
      var obj3 = {};
      for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
      for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }

      return obj3;
    }

    /** Remove objects from FormElement list.
     * @param elementName to remove
     */
    function removeFromFormElementObjectList(elmName) {
      var index = arrayFindObjectIndex(_formElements, 'fieldName', elmName); // find index of object in our array
      if (index >= 0) {
        _formElements.splice(index, 1);
      }
    }

    /** Remove an element from the $validationSummary array
     * @param object validationSummary
     * @param string elmName: element name
     */
    function removeFromValidationSummary(elmName, validationSummaryObj) {
      var self = this;
      var form = getElementParentForm(elmName, self);                         // find the parent form (only found if it has a name)
      var vsObj = validationSummaryObj || _validationSummary;

      var index = arrayFindObjectIndex(vsObj, 'field', elmName); // find index of object in our array
      // if message is empty, remove it from the validation summary object
      if (index >= 0) {
        vsObj.splice(index, 1);
      }
      // also remove from 'local' validationSummary
      index = arrayFindObjectIndex(_validationSummary, 'field', elmName); // find index of object in our array
      if (index >= 0) {
        _validationSummary.splice(index, 1);
      }

      self.scope.$validationSummary = _validationSummary;

      // overwrite the scope form (if found)
      if (!!form) {
        // since validationSummary contain errors of all forms
        // we need to find only the errors of current form and them into the current scope form object
        form.$validationSummary = arrayFindObjects(_validationSummary, 'formName', form.$name);
      }

      // overwrite the ControllerAs alias if it was passed in the global options
      if (!!_globalOptions && !!_globalOptions.controllerAs) {
        _globalOptions.controllerAs.$validationSummary = _validationSummary;

        // also overwrite it inside controllerAs form (if found)
        if (!!form) {
          var formName = form.$name.indexOf('.') >= 0 ? form.$name.split('.')[1] : form.$name;
          if(!!_globalOptions.controllerAs[formName]) {
            _globalOptions.controllerAs[formName].$validationSummary = arrayFindObjects(_validationSummary, 'formName', form.$name);
          }
        }
      }


      return _validationSummary;
    }

    /** Setter on the behaviour of displaying only the last error message of each element.
     * By default this is false, so the behavior is to display all error messages of each element.
     * @param boolean value
     */
    function setDisplayOnlyLastErrorMsg(boolValue) {
      _globalOptions.displayOnlyLastErrorMsg = boolValue;
    }

    /** Set and initialize global options used by all validators
     * @param object attrs: global options
     * @return object self
     */
    function setGlobalOptions(options) {
      var self = this;

      // merge both attributes but 2nd object (attrs) as higher priority, so that for example debounce property inside `attrs` as higher priority over `validatorAttrs`
      // so the position inside the mergeObject call is very important
      _globalOptions = mergeObjects(_globalOptions, options); // save in global

      return self;
    }

    /** in general we will display error message at the next element after our input as <span class="validation validation-inputName text-danger">
      * but in some cases user might want to define which DOM id to display error (as validation attribute)
      * @param string message: error message to display
      * @param object attributes
      */
    function updateErrorMsg(message, attrs) {
      var self = this;
      // attrs.obj if set, should be a commonObj, and can be self.
      // In addition we need to set validatorAttrs, as they are defined as attrs on obj.
      if (!!attrs && attrs.obj) {
        self = attrs.obj;
        self.validatorAttrs = attrs.obj.attrs;
      }

      // element name could be defined in the `attrs` or in the self object
      var elm = (!!attrs && attrs.elm) ? attrs.elm : self.elm;
      var elmName = (!!elm && elm.attr('name')) ? elm.attr('name') : null;

      // Make sure that element has a name="" attribute else it will not work
      if (typeof elmName === "undefined" || elmName === null) {
        var ngModelName = (!!elm) ? elm.attr('ng-model') : 'unknown';
        throw 'Angular-Validation Service requires you to have a (name="") attribute on the element to validate... Your element is: ng-model="' + ngModelName + '"';
      }

      // user might have passed a message to be translated
      var errorMsg = (!!attrs && !!attrs.translate) ? $translate.instant(message) : message;

      // get the name attribute of current element, make sure to strip dirty characters, for example remove a <input name="options[]"/>, we need to strip the "[]"
      var elmInputName = elmName.replace(/[|&;$%@"<>()+,\[\]\{\}]/g, '');
      var errorElm = null;

      // find the element which we'll display the error message, this element might be defined by the user with 'validationErrorTo'
      if (!!self.validatorAttrs && self.validatorAttrs.hasOwnProperty('validationErrorTo')) {
        // validationErrorTo can be used in 3 different ways: with '.' (element error className) or with/without '#' (element error id)
        var firstChar = self.validatorAttrs.validationErrorTo.charAt(0);
        var selector = (firstChar === '.' || firstChar === '#') ? self.validatorAttrs.validationErrorTo : '#' + self.validatorAttrs.validationErrorTo;
        errorElm = angular.element(document.querySelector(selector));
      }
      // errorElm can be empty due to:
      //  1. validationErrorTo has not been set
      //  2. validationErrorTo has been mistyped, and if mistyped, use regular functionality
      if (!errorElm || errorElm.length === 0) {
        // most common way, let's try to find our <span class="validation-inputName">
        errorElm = angular.element(document.querySelector('.validation-' + elmInputName));
      }

      // form might have already been submitted
      var isSubmitted = (!!attrs && attrs.isSubmitted) ? attrs.isSubmitted : false;

      // invalid & isDirty, display the error message... if <span> not exist then create it, else udpate the <span> text
      if (!!attrs && !attrs.isValid && (isSubmitted || self.ctrl.$dirty || self.ctrl.$touched)) {
        (errorElm.length > 0) ? errorElm.html(errorMsg) : elm.after('<span class="validation validation-' + elmInputName + ' text-danger">' + errorMsg + '</span>');
      } else {
        errorElm.html('');  // element is pristine or no validation applied, error message has to be blank
      }
    }

    /** Validate function, from the input value it will go through all validators (separated by pipe)
     *  that were passed to the input element and will validate it. If field is invalid it will update
     *  the error text of the span/div element dedicated for that error display.
     * @param string value: value of the input field
     * @param bool showError: do we want to show the error or hide it (false is useful for adding error to $validationSummary without displaying it on screen)
     * @return bool isFieldValid
     */
    function validate(strValue, showError) {
      var self = this;
      var isValid = true;
      var isFieldValid = true;
      var message = '';
      var regex;
      var validator;

      // to make proper validation, our element value cannot be an undefined variable (we will at minimum make it an empty string)
      // For example, in some particular cases "undefined" returns always True on regex.test() which is incorrect especiall on max_len:x
      if (typeof strValue === "undefined") {
        strValue = '';
      }

      // get some common variables
      var elmName = (!!self.ctrl && !!self.ctrl.$name)
        ? self.ctrl.$name
        : (!!self.attrs && !!self.attrs.name)
          ? self.attrs.name
          : self.elm.attr('name');

      var formElmObj = getFormElementByName(elmName);
      var rules = self.validatorAttrs.rules || self.validatorAttrs.validation;

      // loop through all validators (could be multiple)
      for (var j = 0, jln = self.validators.length; j < jln; j++) {
        validator = self.validators[j];

        // the AutoDetect type is a special case and will detect if the given value is of type numeric or not.
        // then it will rewrite the conditions or regex pattern, depending on type found
        if (validator.type === "autoDetect") {
          if (isNumeric(strValue)) {
            validator = {
              condition: validator.conditionNum,
              message: validator.messageNum,
              params: validator.params,
              type: "conditionalNumber"
            };
          }else {
            validator = {
              pattern: validator.patternLength,
              message: validator.messageLength,
              params: validator.params,
              type: "regex"
            };
          }
        }

        // now that we have a Validator type, we can now validate our value
        // there is multiple type that can influence how the value will be validated

        if (validator.type === "conditionalDate") {
          var isWellFormed = isValid = false;

          // 1- make sure Date is well formed (if it's already a Date object then it's already good, else check that with Regex)
          if((strValue instanceof Date)) {
            isWellFormed = true;
          }else {
            // run the Regex test through each iteration, if required (\S+) and is null then it's invalid automatically
            regex = new RegExp(validator.pattern);
            isWellFormed = ((!validator.pattern || validator.pattern.toString() === "/\\S+/" || (!!rules && validator.pattern === "required")) && strValue === null) ? false : regex.test(strValue);
          }

          // 2- date is well formed, then go ahead with conditional date check
          if (isWellFormed) {
            // For Date comparison, we will need to construct a Date Object that follows the ECMA so then it could work in all browser
            // Then convert to timestamp & finally we can compare both dates for filtering
            var dateType = validator.dateType;                   // date type (ISO, EURO, US-SHORT, US-LONG)
            var timestampValue = (strValue instanceof Date) ? strValue : parseDate(strValue, dateType).getTime(); // our input value parsed into a timestamp

            // if 2 params, then it's a between condition
            if (validator.params.length == 2) {
              // this is typically a "between" condition, a range of number >= and <=
              var timestampParam0 = parseDate(validator.params[0], dateType).getTime();
              var timestampParam1 = parseDate(validator.params[1], dateType).getTime();
              var isValid1 = testCondition(validator.condition[0], timestampValue, timestampParam0);
              var isValid2 = testCondition(validator.condition[1], timestampValue, timestampParam1);
              isValid = (isValid1 && isValid2) ? true : false;
            } else {
              // else, 1 param is a simple conditional date check
              var timestampParam = parseDate(validator.params[0], dateType).getTime();
              isValid = testCondition(validator.condition, timestampValue, timestampParam);
            }
          }
        }
        // it might be a conditional number checking
        else if (validator.type === "conditionalNumber") {
          // if 2 params, then it's a between condition
          if (validator.params.length == 2) {
            // this is typically a "between" condition, a range of number >= and <=
            var isValid1 = testCondition(validator.condition[0], parseFloat(strValue), parseFloat(validator.params[0]));
            var isValid2 = testCondition(validator.condition[1], parseFloat(strValue), parseFloat(validator.params[1]));
            isValid = (isValid1 && isValid2) ? true : false;
          } else {
            // else, 1 param is a simple conditional number check
            isValid = testCondition(validator.condition, parseFloat(strValue), parseFloat(validator.params[0]));
          }
        }
        // it might be a match input checking
        else if (validator.type === "matching") {
          // get the element 'value' ngModel to compare to (passed as params[0], via an $eval('ng-model="modelToCompareName"')
          var otherNgModel = validator.params[0];
          var otherNgModelVal = self.scope.$eval(otherNgModel);
          var elm = angular.element(document.querySelector('[name="'+otherNgModel+'"]'));

          isValid = (testCondition(validator.condition, strValue, otherNgModelVal) && !!strValue);

          // if element to compare against has a friendlyName or if matching 2nd argument was passed, we will use that as a new friendlyName
          // ex.: <input name='input1' friendly-name='Password1'/> :: we would use the friendlyName of 'Password1' not input1
          // or <input name='confirm_pass' validation='match:input1,Password2' /> :: we would use Password2 not input1
          if(!!elm && !!elm.attr('friendly-name')) {
            validator.params[1] = elm.attr('friendly-name');
          }
          else if(validator.params.length > 1)  {
            validator.params[1] = validator.params[1];
          }
        }
        // it might be a remote validation, this should return a promise with the result as a boolean or a { isValid: bool, message: msg }
        else if (validator.type === "remote") {
          if (!!strValue && !!showError) {
            self.ctrl.$processing = true; // $processing can be use in the DOM to display a remote processing message to the user

            var fct = null;
            var fname = validator.params[0];
            if (fname.indexOf(".") === -1) {
              fct = self.scope[fname];
            } else {
              // function name might also be declared with the Controller As alias, for example: vm.customRemote()
              // split the name and flatten it to find it inside the scope
              var split = fname.split('.');
              fct = self.scope;
              for (var k = 0, kln = split.length; k < kln; k++) {
                fct = fct[split[k]];
              }
            }
            var promise = (typeof fct === "function") ? fct() : null;

            // if we already have previous promises running, we might want to abort them (if user specified an abort function)
            if (_remotePromises.length > 1) {
              while (_remotePromises.length > 0) {
                var previousPromise = _remotePromises.pop();
                if (typeof previousPromise.abort === "function") {
                  previousPromise.abort(); // run the abort if user declared it
                }
              }
            }
            _remotePromises.push(promise); // always add to beginning of array list of promises

            if (!!promise && typeof promise.then === "function") {
              self.ctrl.$setValidity('remote', false); // make the field invalid before processing it

              // process the promise
              (function (altText) {
                promise.then(function (result) {
                  result = result.data || result;
                  _remotePromises.pop();                 // remove the last promise from array list of promises

                  self.ctrl.$processing = false;  // finished resolving, no more pending
                  var errorMsg = message + ' ';   // use the global error message

                  if (typeof result === "boolean") {
                    isValid = (!!result) ? true : false;
                  } else if (typeof result === "object") {
                    isValid = (!!result.isValid) ? true : false;
                  }

                  if (isValid === false) {
                    formElmObj.isValid = false;
                    errorMsg += result.message || altText;

                    // is field is invalid and we have an error message given, then add it to validationSummary and display error
                    addToValidationAndDisplayError(self, formElmObj, errorMsg, false, showError);
                  }
                  if (isValid === true && isFieldValid === true) {
                    // if field is valid from the remote check (isValid) and from the other validators check (isFieldValid)
                    // clear up the error message and make the field directly as Valid with $setValidity since remote check arrive after all other validators check
                    formElmObj.isValid = true;
                    self.ctrl.$setValidity('remote', true);
                    addToValidationAndDisplayError(self, formElmObj, '', true, showError);
                  }
                });
              })(validator.altText);
            } else {
              throw 'Remote Validation requires a declared function (in your Controller) which also needs to return a Promise, please review your code.'
            }
          }
        }
        // or finally it might be a regular regex pattern checking
        else {
          // get the ngDisabled attribute if found
          var elmAttrNgDisabled = (!!self.attrs) ? self.attrs.ngDisabled : self.validatorAttrs.ngDisabled;

          // a 'disabled' element should always be valid, there is no need to validate it
          if (!!self.elm.prop("disabled") || !!self.scope.$eval(elmAttrNgDisabled)) {
            isValid = true;
          } else {
            // before running Regex test, we'll make sure that an input of type="number" doesn't hold invalid keyboard chars, if true skip Regex
            if (typeof strValue === "string" && strValue === "" && self.elm.prop('type').toUpperCase() === "NUMBER") {
              isValid = false;
              //message = $translate.instant("INVALID_KEY_CHAR");
            } else {
              // run the Regex test through each iteration, if required (\S+) and is null then it's invalid automatically
              regex = new RegExp(validator.pattern);
              isValid = ((!validator.pattern || validator.pattern.toString() === "/\\S+/" || (!!rules && validator.pattern === "required")) && strValue === null) ? false : regex.test(strValue);
            }
          }
        }

        // not required and not filled is always valid & 'disabled', 'ng-disabled' elements should always be valid
        if ((!self.bFieldRequired && !strValue) || (!!self.elm.prop("disabled") || !!self.scope.$eval(elmAttrNgDisabled))) {
          isValid = true;
        }

        if (!isValid) {
          isFieldValid = false;

          // run $translate promise, use closures to keep access to all necessary variables
          (function (formElmObj, isValid, validator) {
            var msgToTranslate = validator.message;
            if (!!validator.altText && validator.altText.length > 0) {
              msgToTranslate = validator.altText.replace("alt=", "");
            }

            $translate(msgToTranslate).then(function (translation) {
              // if user is requesting to see only the last error message, we will use '=' instead of usually concatenating with '+='
              // then if validator rules has 'params' filled, then replace them inside the translation message (foo{0} {1}...), same syntax as String.format() in C#
              if (message.length > 0 && _globalOptions.displayOnlyLastErrorMsg) {
                message = ' ' + ((!!validator && !!validator.params) ? String.format(translation, validator.params) : translation);
              } else {
                message += ' ' + ((!!validator && !!validator.params) ? String.format(translation, validator.params) : translation);
              }
              addToValidationAndDisplayError(self, formElmObj, message, isFieldValid, showError);
            })
            ["catch"](function (data) {
              // error caught:
              // alternate text might not need translation if the user sent his own custom message or is already translated
              // so just send it directly into the validation summary.
              if (!!validator.altText && validator.altText.length > 0) {
                // if user is requesting to see only the last error message
                if (message.length > 0 && _globalOptions.displayOnlyLastErrorMsg) {
                  message = ' ' + msgToTranslate;
                } else {
                  message += ' ' + msgToTranslate;
                }
                addToValidationAndDisplayError(self, formElmObj, message, isFieldValid, showError);
              }
            });
          })(formElmObj, isValid, validator);
        } // if(!isValid)
      }   // for() loop

      // only log the invalid message in the $validationSummary
      if (isValid) {
        addToValidationSummary(self, '');
        self.updateErrorMsg('', { isValid: isValid });
      }

      if (!!formElmObj) {
        formElmObj.isValid = isFieldValid;
        if (isFieldValid) {
          formElmObj.message = '';
        }
      }
      return isFieldValid;
    } // validate()

    //----
    // Private functions declaration
    //----------------------------------

    /** Add to the Form Elements Array of Object List
     * @param object elm
     * @param object attrs
     * @param object ctrl
     */
    function addToFormElementObjectList(elm, attrs, ctrl, scope) {
      var elmName = (!!attrs.name) ? attrs.name : elm.attr('name');
      var form = getElementParentForm(elmName, { scope: scope });                         // find the parent form (only found if it has a name)
      var friendlyName = (!!attrs && !!attrs.friendlyName) ? $translate.instant(attrs.friendlyName) : '';
      var formElm = { fieldName: elmName, friendlyName: friendlyName, elm: elm, attrs: attrs, ctrl: ctrl, scope: scope, isValid: false, message: '', formName: (!!form) ? form.$name : null };
      var index = arrayFindObjectIndex(_formElements, 'fieldName', elm.attr('name')); // find index of object in our array
      if (index >= 0) {
        _formElements[index] = formElm;
      } else {
        _formElements.push(formElm);
      }
      return _formElements;
    }

    /** Will add error to the validationSummary and also display the error message if requested
     * @param object self
     * @param object formElmObj
     * @param string message: error message
     * @param bool showError
     */
    function addToValidationAndDisplayError(self, formElmObj, message, isFieldValid, showError) {
      // trim any white space
      message = message.trim();

      // log the invalid message in the $validationSummary
      addToValidationSummary(formElmObj, message);

      // change the Form element object boolean flag from the `formElements` variable, used in the `checkFormValidity()`
      if (!!formElmObj) {
        formElmObj.message = message;
      }

      // if user is pre-validating all form elements, display error right away
      if (!!self.validatorAttrs.preValidateFormElements || !!_globalOptions.preValidateFormElements) {
        // make the element as it was touched for CSS, only works in AngularJS 1.3+
        if (!!formElmObj && typeof self.ctrl.$setTouched === "function") {
          formElmObj.ctrl.$setTouched();
        }
        // only display errors on page load, when elements are not yet dirty
        if (self.ctrl.$dirty === false) {
          updateErrorMsg(message, { isSubmitted: true, isValid: isFieldValid, obj: formElmObj });
        }
      }

      // error Display
      if (showError && !!formElmObj && !formElmObj.isValid) {
        self.updateErrorMsg(message, { isValid: isFieldValid });
      } else if (!!formElmObj && formElmObj.isValid) {
        addToValidationSummary(formElmObj, '');
      }
    }

    /** Add the error to the validation summary
     * @param object self
     * @param string message: error message
     */
    function addToValidationSummary(self, message) {
      if (typeof self === "undefined" || self == null) {
        return;
      }

      // get the element name, whichever we find it
      var elmName = (!!self.ctrl && !!self.ctrl.$name)
        ? self.ctrl.$name
        : (!!self.attrs && !!self.attrs.name)
          ? self.attrs.name
          : self.elm.attr('name');

      var form = getElementParentForm(elmName, self);                         // find the parent form (only found if it has a name)
      var index = arrayFindObjectIndex(_validationSummary, 'field', elmName);  // find index of object in our array

      // if message is empty, remove it from the validation summary
      if (index >= 0 && message === '') {
        _validationSummary.splice(index, 1);
      } else if (message !== '') {
        var friendlyName = (!!self.attrs && !!self.friendlyName) ? $translate.instant(self.friendlyName) : '';
        var errorObj = { field: elmName, friendlyName: friendlyName, message: message, formName: (!!form) ? form.$name : null };

        // if error already exist then refresh the error object inside the array, else push it to the array
        if (index >= 0) {
          _validationSummary[index] = errorObj;
        } else {
          _validationSummary.push(errorObj);
        }
      }

      // save validation summary into scope root
      self.scope.$validationSummary = _validationSummary;

      // and also save it inside the current scope form (if found)
      if (!!form) {
        // since validationSummary contain errors of all forms
        // we need to find only the errors of current form and them into the current scope form object
        form.$validationSummary = arrayFindObjects(_validationSummary, 'formName', form.$name);
      }

      // also save it inside the ControllerAs alias if it was passed in the global options
      if (!!_globalOptions && !!_globalOptions.controllerAs) {
        _globalOptions.controllerAs.$validationSummary = _validationSummary;

        // also save it inside controllerAs form (if found)
        if (!!form) {
          var formName = form.$name.indexOf('.') >= 0 ? form.$name.split('.')[1] : form.$name;
          var ctrlForm = (!!_globalOptions.controllerAs[formName]) ? _globalOptions.controllerAs[formName] : self.elm.controller()[formName];
          ctrlForm.$validationSummary = arrayFindObjects(_validationSummary, 'formName', form.$name);
        }
      }

      return _validationSummary;
    }

    /** Quick function to find an object inside an array by it's given field name and value, return the object found or null
     * @param Array sourceArray
     * @param string searchId: search property id
     * @param string searchValue: value to search
     * @return object found from source array or null
     */
    function arrayFindObject(sourceArray, searchId, searchValue) {
      if (!!sourceArray) {
        for (var i = 0; i < sourceArray.length; i++) {
          if (sourceArray[i][searchId] === searchValue) {
            return sourceArray[i];
          }
        }
      }
      return null;
    }

    /** Quick function to find all object(s) inside an array of objects by it's given field name and value, return array of object found(s) or empty array
     * @param Array sourceArray
     * @param string searchId: search property id
     * @param string searchValue: value to search
     * @return array of object found from source array
     */
    function arrayFindObjects(sourceArray, searchId, searchValue) {
      var results = [];
      if (!!sourceArray) {
        for (var i = 0; i < sourceArray.length; i++) {
          if (sourceArray[i][searchId] === searchValue) {
            results.push(sourceArray[i]);
          }
        }
      }
      return results;
    }

    /** Quick function to find an object inside an array by it's given field name and value, return the index position found or -1
     * @param Array sourceArray
     * @param string searchId: search property id
     * @param string searchValue: value to search
     * @return int index position found
     */
    function arrayFindObjectIndex(sourceArray, searchId, searchValue) {
      if (!!sourceArray) {
        for (var i = 0; i < sourceArray.length; i++) {
          if (sourceArray[i][searchId] === searchValue) {
            return i;
          }
        }
      }

      return -1;
    }

    /** Explode a '.' dot notation string to an object
     * @param string str
     * @parem object
     * @return object
     */
    function explodedDotNotationStringToObject(str, obj) {
      var split = str.split('.');

      for (var k = 0, kln = split.length; k < kln; k++) {
        if(!!obj[split[k]]) {
          obj = obj[split[k]];
        }
      }
      return obj;
    }

    /** Get the element's parent Angular form (if found)
     * @param object self
     * @return object scope form
     */
    function getElementParentForm(elmName, self) {
      // from the element passed, get his parent form
      var forms = document.getElementsByName(elmName);
      var parentForm = null;

      for (var i = 0; i < forms.length; i++) {
        var form = forms[i].form;

        if (!!form && !!form.name) {
          parentForm = (!!_globalOptions && !!_globalOptions.controllerAs && form.name.indexOf('.') >= 0)
            ? explodedDotNotationStringToObject(form.name, self.scope)
            : self.scope[form.name];

          if(!!parentForm) {
            if (typeof parentForm.$name === "undefined") {
              parentForm.$name = form.name; // make sure it has a $name, since we use that variable later on
            }
            return parentForm;
          }
        }
      }

      // falling here with a form name but without a form object found in the scope is often due to isolate scope
      // we can hack it and define our own form inside this isolate scope, in that way we can still use something like: isolateScope.form1.$validationSummary
      if (!!form && !!form.name) {
        var obj = { $name: form.name, specialNote: 'Created by Angular-Validation for Isolated Scope usage' };

        if (!!_globalOptions && !!_globalOptions.controllerAs && form.name.indexOf('.') >= 0) {
          var formSplit = form.name.split('.');
          return self.scope[formSplit[0]][formSplit[1]] = obj
        }
        return self.scope[form.name] = obj;
      }
      return null;
    }

    /** Check if the given argument is numeric
     * @param mixed n
     * @return bool
     */
    function isNumeric(n) {
      return !isNaN(parseFloat(n)) && isFinite(n);
    }

    /** Parse a date from a String and return it as a Date Object to be valid for all browsers following ECMA Specs
     * Date type ISO (default), US, UK, Europe, etc... Other format could be added in the switch case
     * @param String dateStr: date String
     * @param String dateType: date type (ISO, US, etc...)
     * @return object date
     */
    function parseDate(dateStr, dateType) {
      // variables declaration
      var dateSubStr = '', dateSeparator = '-', dateSplit = [], timeSplit = [], year = '', month = '', day = '';

      // Parse using the date type user selected, (separator could be dot, slash or dash)
      switch (dateType.toUpperCase()) {
        case 'EURO_LONG':
        case 'EURO-LONG': // UK, Europe long format is: dd/mm/yyyy hh:mm:ss
          dateSubStr = dateStr.substring(0, 10);
          dateSeparator = dateStr.substring(2, 3);
          dateSplit = splitDateString(dateSubStr, dateSeparator);
          day = dateSplit[0];
          month = dateSplit[1];
          year = dateSplit[2];
          timeSplit = (dateStr.length > 8) ? dateStr.substring(9).split(':') : null;
          break;
        case 'UK':
        case 'EURO':
        case 'EURO_SHORT':
        case 'EURO-SHORT':
        case 'EUROPE':  // UK, Europe format is: dd/mm/yy hh:mm:ss
          dateSubStr = dateStr.substring(0, 8);
          dateSeparator = dateStr.substring(2, 3);
          dateSplit = splitDateString(dateSubStr, dateSeparator);
          day = dateSplit[0];
          month = dateSplit[1];
          year = (parseInt(dateSplit[2]) < 50) ? ('20' + dateSplit[2]) : ('19' + dateSplit[2]); // below 50 we'll consider that as century 2000's, else in century 1900's
          timeSplit = (dateStr.length > 8) ? dateStr.substring(9).split(':') : null;
          break;
        case 'US_LONG':
        case 'US-LONG':    // US long format is: mm/dd/yyyy hh:mm:ss
          dateSubStr = dateStr.substring(0, 10);
          dateSeparator = dateStr.substring(2, 3);
          dateSplit = splitDateString(dateSubStr, dateSeparator);
          month = dateSplit[0];
          day = dateSplit[1];
          year = dateSplit[2];
          timeSplit = (dateStr.length > 8) ? dateStr.substring(9).split(':') : null;
          break;
        case 'US':
        case 'US_SHORT':
        case 'US-SHORT':    // US short format is: mm/dd/yy hh:mm:ss OR
          dateSubStr = dateStr.substring(0, 8);
          dateSeparator = dateStr.substring(2, 3);
          dateSplit = splitDateString(dateSubStr, dateSeparator);
          month = dateSplit[0];
          day = dateSplit[1];
          year = (parseInt(dateSplit[2]) < 50) ? ('20' + dateSplit[2]) : ('19' + dateSplit[2]); // below 50 we'll consider that as century 2000's, else in century 1900's
          timeSplit = (dateStr.length > 8) ? dateStr.substring(9).split(':') : null;
          break;
        case 'ISO':
        default:    // ISO format is: yyyy-mm-dd hh:mm:ss (separator could be dot, slash or dash: ".", "/", "-")
          dateSubStr = dateStr.substring(0, 10);
          dateSeparator = dateStr.substring(4, 5);
          dateSplit = splitDateString(dateSubStr, dateSeparator);
          year = dateSplit[0];
          month = dateSplit[1];
          day = dateSplit[2];
          timeSplit = (dateStr.length > 10) ? dateStr.substring(11).split(':') : null;
          break;
      }

      // parse the time if it exist else put them at 0
      var hour = (!!timeSplit && timeSplit.length === 3) ? timeSplit[0] : 0;
      var min = (!!timeSplit && timeSplit.length === 3) ? timeSplit[1] : 0;
      var sec = (!!timeSplit && timeSplit.length === 3) ? timeSplit[2] : 0;

      // Construct a valid Date Object that follows the ECMA Specs
      // Note that, in JavaScript, months run from 0 to 11, rather than 1 to 12!
      return new Date(year, month - 1, day, hour, min, sec);
    }

    /** From a date substring split it by a given separator and return a split array
     * @param string dateSubStr
     * @param string dateSeparator
     * @return array date splitted
     */
    function splitDateString(dateSubStr, dateSeparator) {
      var dateSplit = [];

      switch (dateSeparator) {
        case '/':
          dateSplit = dateSubStr.split('/'); break;
        case '.':
          dateSplit = dateSubStr.split('.'); break;
        case '-':
        default:
          dateSplit = dateSubStr.split('-'); break;
      }

      return dateSplit;
    }

    /** Test values with condition, I have created a switch case for all possible conditions.
     * @param string condition: condition to filter with
     * @param any value1: 1st value to compare, the type could be anything (number, String or even Date)
     * @param any value2: 2nd value to compare, the type could be anything (number, String or even Date)
     * @return boolean: a boolean result of the tested condition (true/false)
     */
    function testCondition(condition, value1, value2) {
      var result = false;

      switch (condition) {
        case '<': result = (value1 < value2) ? true : false; break;
        case '<=': result = (value1 <= value2) ? true : false; break;
        case '>': result = (value1 > value2) ? true : false; break;
        case '>=': result = (value1 >= value2) ? true : false; break;
        case '!=':
        case '<>': result = (value1 != value2) ? true : false; break;
        case '!==': result = (value1 !== value2) ? true : false; break;
        case '=':
        case '==': result = (value1 == value2) ? true : false; break;
        case '===': result = (value1 === value2) ? true : false; break;
        default: result = false; break;
      }
      return result;
    }

    /** Override javascript trim() function so that it works accross all browser platforms */
    function stringPrototypeTrim() {
      return this.replace(/^\s+|\s+$/g, '');
    }

    /** Override javascript format() function to be the same as the effect as the C# String.Format
     * Input: "Some {0} are showing {1}".format("inputs", "invalid");
     * Output: "Some inputs are showing invalid"
     * @param string
     * @param replacements
      */
    function stringPrototypeFormat() {
      var args = (Array.isArray(arguments[0])) ? arguments[0] : arguments;
      return this.replace(/{(\d+)}/g, function (match, number) {
        return (!!args[number]) ? args[number] : match;
      });
    }

    /** Override javascript String.format() function to be the same as the effect as the C# String.Format
     * Input: String.format("Some {0} are showing {1}", "inputs", "invalid");
     * Output: "Some inputs are showing invalid"
     * @param string
     * @param replacements
      */
    function stringFormat(format) {
      var args = (Array.isArray(arguments[1])) ? arguments[1] : Array.prototype.slice.call(arguments, 1);

      return format.replace(/{(\d+)}/g, function (match, number) {
        return (!!args[number]) ? args[number] : match;
      });
    }

  }]); // validationCommon service


  /**
 * angular-validation-rules (ghiscoding)
 * https://github.com/ghiscoding/angular-validation
 *
 * @author: Ghislain B.
 * @desc: angular-validation rules definition
 * Each rule objects must have 3 properties {pattern, message, type}
 * and in some cases you could also define a 4th properties {params} to pass extras, for example: max_len will know his maximum length by this extra {params}
 * Rule.type can be {autoDetect, conditionalDate, conditionalNumber, match, regex}
 *
 * WARNING: Rule patterns are defined as String type so don't forget to escape your characters: \\
 */
angular
  .module('ghiscoding.validation')
  .factory('validationRules', [function () {
    // return the service object
    var service = {
      getElementValidators: getElementValidators
    };
    return service;

    //----
    // Functions declaration
    //----------------------------------

    /** Get the element active validators and store it inside an array which will be returned
     * @param object args: all attributes
     */
    function getElementValidators(args) {
      // grab all passed attributes
      var alternateText = (typeof args.altText !== "undefined") ? args.altText.replace("alt=", "") : null;
      var customUserRegEx = (args.hasOwnProperty('customRegEx')) ? args.customRegEx : null;
      var rule = (args.hasOwnProperty('rule')) ? args.rule : null;
      var ruleParams = (args.hasOwnProperty('ruleParams')) ? args.ruleParams : null;

      // validators on the current DOM element, an element can have 1+ validators
      var validator = {};

      switch(rule) {
        case "accepted":
          validator = {
            pattern: /^(yes|on|1|true)$/i,
            message: "INVALID_ACCEPTED",
            type: "regex"
          };
          break;
        case "alpha" :
          validator = {
            pattern: /^([a-zа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ])+$/i,
            message: "INVALID_ALPHA",
            type: "regex"
          };
          break;
        case "alphaSpaces" :
        case "alpha_spaces" :
          validator = {
            pattern: /^([a-zа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ\s])+$/i,
            message: "INVALID_ALPHA_SPACE",
            type: "regex"
          };
          break;
        case "alphaNum" :
        case "alpha_num" :
          validator = {
            pattern: /^([a-zа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ0-9])+$/i,
            message: "INVALID_ALPHA_NUM",
            type: "regex"
          };
          break;
        case "alphaNumSpaces" :
        case "alpha_num_spaces" :
          validator = {
            pattern: /^([a-zа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ0-9\s])+$/i,
            message: "INVALID_ALPHA_NUM_SPACE",
            type: "regex"
          };
          break;
        case "alphaDash" :
        case "alpha_dash" :
          validator = {
            pattern: /^([a-zа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ0-9_-])+$/i,
            message: "INVALID_ALPHA_DASH",
            type: "regex"
          };
          break;
        case "alphaDashSpaces" :
        case "alpha_dash_spaces" :
          validator = {
            pattern: /^([a-zа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ0-9\s_-])+$/i,
            message: "INVALID_ALPHA_DASH_SPACE",
            type: "regex"
          };
          break;
        case "between" :
          var ranges = ruleParams.split(',');
          if (ranges.length !== 2) {
            throw "This validation must include exactly 2 params separated by a comma (,) ex.: between:1,5";
          }
          validator = {
            patternLength: "^(.|[\\r\\n]){" + ranges[0] + "," + ranges[1] + "}$",
            messageLength: "INVALID_BETWEEN_CHAR",
            conditionNum: [">=","<="],
            messageNum: "INVALID_BETWEEN_NUM",
            params: [ranges[0], ranges[1]],
            type: "autoDetect"
          };
          break;
        case "betweenLen" :
        case "between_len" :
          var ranges = ruleParams.split(',');
          if (ranges.length !== 2) {
            throw "This validation must include exactly 2 params separated by a comma (,) ex.: between_len:1,5";
          }
          validator = {
            pattern: "^(.|[\\r\\n]){" + ranges[0] + "," + ranges[1] + "}$",
            message: "INVALID_BETWEEN_CHAR",
            params: [ranges[0], ranges[1]],
            type: "regex"
          };
          break;
        case "betweenNum" :
        case "between_num" :
          var ranges = ruleParams.split(',');
          if (ranges.length !== 2) {
            throw "This validation must include exactly 2 params separated by a comma (,) ex.: between_num:1,5";
          }
          validator = {
            condition: [">=","<="],
            message: "INVALID_BETWEEN_NUM",
            params: [ranges[0], ranges[1]],
            type: "conditionalNumber"
          };
          break;
        case "boolean":
          validator = {
            pattern: /^(true|false|0|1)$/i,
            message: "INVALID_BOOLEAN",
            type: "regex"
          };
          break;
        case "checked":
          validator = {
            pattern: /^true$/i,
            message: "INVALID_CHECKBOX_SELECTED",
            type: "regex"
          };
          break;
        case "creditCard" :
        case "credit_card" :
          validator = {
            pattern: /^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|6(?:011|5[0-9]{2})[0-9]{12}|(?:2131|1800|35\d{3})\d{11})$/,
            message: "INVALID_CREDIT_CARD",
            type: "regex"
          };
          break;
        case "dateEuroLong" :
        case "date_euro_long" :
          validator = {
            pattern: /^(0[1-9]|[12][0-9]|3[01])[-\/\.](0[1-9]|1[012])[-\/\.](19|20)\d\d$/,
            message: "INVALID_DATE_EURO_LONG",
            type: "regex"
          };
          break;
        case "dateEuroLongBetween" :
        case "date_euro_long_between" :
        case "betweenDateEuroLong" :
        case "between_date_euro_long" :
          var ranges = ruleParams.split(',');
          if (ranges.length !== 2) {
            throw "This validation must include exactly 2 params separated by a comma (,) ex.: between_date_euro_long:01-01-1990,31-12-2015";
          }
          validator = {
            condition: [">=","<="],
            dateType: "EURO_LONG",
            params: [ranges[0], ranges[1]],
            pattern: /^(0[1-9]|[12][0-9]|3[01])[-\/\.](0[1-9]|1[012])[-\/\.](19|20)\d\d$/,
            message: "INVALID_DATE_EURO_LONG_BETWEEN",
            type: "conditionalDate"
          };
          break;
        case "dateEuroLongMax" :
        case "date_euro_long_max" :
        case "maxDateEuroLong" :
        case "max_date_euro_long" :
          validator = {
            condition: "<=",
            dateType: "EURO_LONG",
            params: [ruleParams],
            pattern: /^(0[1-9]|[12][0-9]|3[01])[-\/\.](0[1-9]|1[012])[-\/\.](19|20)\d\d$/,
            message: "INVALID_DATE_EURO_LONG_MAX",
            type: "conditionalDate"
          };
          break;
        case "dateEuroLongMin" :
        case "date_euro_long_min" :
        case "minDateEuroLong" :
        case "min_date_euro_long" :
          validator = {
            condition: ">=",
            dateType: "EURO_LONG",
            params: [ruleParams],
            pattern: /^(0[1-9]|[12][0-9]|3[01])[-\/\.](0[1-9]|1[012])[-\/\.](19|20)\d\d$/,
            message: "INVALID_DATE_EURO_LONG_MIN",
            type: "conditionalDate"
          };
          break;
        case "dateEuroShort" :
        case "date_euro_short" :
          validator = {
            pattern: /^(0[1-9]|[12][0-9]|3[01])[-\/\.](0[1-9]|1[012])[-\/\.]\d\d$/,
            message: "INVALID_DATE_EURO_SHORT",
            type: "regex"
          };
          break;
        case "dateEuroShortBetween" :
        case "date_euro_short_between" :
        case "betweenDateEuroShort" :
        case "between_date_euro_short" :
          var ranges = ruleParams.split(',');
          if (ranges.length !== 2) {
            throw "This validation must include exactly 2 params separated by a comma (,) ex.: between_date_euro_short:01-01-90,31-12-15";
          }
          validator = {
            condition: [">=","<="],
            dateType: "EURO_SHORT",
            params: [ranges[0], ranges[1]],
            pattern: /^(0[1-9]|[12][0-9]|3[01])[-\/\.](0[1-9]|1[012])[-\/\.]\d\d$/,
            message: "INVALID_DATE_EURO_SHORT_BETWEEN",
            type: "conditionalDate"
          };
          break;
        case "dateEuroShortMax" :
        case "date_euro_short_max" :
        case "maxDateEuroShort" :
        case "max_date_euro_short" :
          validator = {
            condition: "<=",
            dateType: "EURO_SHORT",
            params: [ruleParams],
            pattern: /^(0[1-9]|[12][0-9]|3[01])[-\/\.](0[1-9]|1[012])[-\/\.]\d\d$/,
            message: "INVALID_DATE_EURO_SHORT_MAX",
            type: "conditionalDate"
          };
          break;
        case "dateEuroShortMin" :
        case "date_euro_short_min" :
        case "minDateEuroShort" :
        case "min_date_euro_short" :
          validator = {
            condition: ">=",
            dateType: "EURO_SHORT",
            params: [ruleParams],
            pattern: /^(0[1-9]|[12][0-9]|3[01])[-\/\.](0[1-9]|1[012])[-\/\.]\d\d$/,
            message: "INVALID_DATE_EURO_SHORT_MIN",
            type: "conditionalDate"
          };
          break;
        case "dateIso" :
        case "date_iso" :
          validator = {
            pattern: /^(19|20)\d\d([-])(0[1-9]|1[012])\2(0[1-9]|[12][0-9]|3[01])$/,
            message: "INVALID_DATE_ISO",
            type: "regex"
          };
          break;
        case "dateIsoBetween" :
        case "date_iso_between" :
        case "betweenDateIso" :
        case "between_date_iso" :
          var ranges = ruleParams.split(',');
          if (ranges.length !== 2) {
            throw "This validation must include exactly 2 params separated by a comma (,) ex.: between_date_iso:1990-01-01,2000-12-31";
          }
          validator = {
            condition: [">=","<="],
            dateType: "ISO",
            params: [ranges[0], ranges[1]],
            pattern: /^(19|20)\d\d([-])(0[1-9]|1[012])\2(0[1-9]|[12][0-9]|3[01])$/,
            message: "INVALID_DATE_ISO_BETWEEN",
            type: "conditionalDate"
          };
          break;
        case "dateIsoMax" :
        case "date_iso_max" :
        case "maxDateIso" :
        case "max_date_iso" :
          validator = {
            condition: "<=",
            dateType: "ISO",
            params: [ruleParams],
            pattern: /^(19|20)\d\d([-])(0[1-9]|1[012])\2(0[1-9]|[12][0-9]|3[01])$/,
            message: "INVALID_DATE_ISO_MAX",
            type: "conditionalDate"
          };
          break;
        case "dateIsoMin" :
        case "date_iso_min" :
        case "minDateIso" :
        case "min_date_iso" :
          validator = {
            condition: ">=",
            dateType: "ISO",
            params: [ruleParams],
            pattern: /^(19|20)\d\d([-])(0[1-9]|1[012])\2(0[1-9]|[12][0-9]|3[01])$/,
            message: "INVALID_DATE_ISO_MIN",
            type: "conditionalDate"
          };
          break;
        case "dateUsLong" :
        case "date_us_long" :
          validator = {
            pattern: /^(0[1-9]|1[012])[-\/\.](0[1-9]|[12][0-9]|3[01])[-\/\.](19|20)\d\d$/,
            message: "INVALID_DATE_US_LONG",
            type: "regex"
          };
          break;
        case "dateUsLongBetween" :
        case "date_us_long_between" :
        case "betweenDateUsLong" :
        case "between_date_us_long" :
          var ranges = ruleParams.split(',');
          if (ranges.length !== 2) {
            throw "This validation must include exactly 2 params separated by a comma (,) ex.: between_date_us_long:01/01/1990,12/31/2015";
          }
          validator = {
            condition: [">=","<="],
            dateType: "US_LONG",
            params: [ranges[0], ranges[1]],
            pattern: /^(0[1-9]|1[012])[-\/\.](0[1-9]|[12][0-9]|3[01])[-\/\.](19|20)\d\d$/,
            message: "INVALID_DATE_US_LONG_BETWEEN",
            type: "conditionalDate"
          };
          break;
        case "dateUsLongMax" :
        case "date_us_long_max" :
        case "maxDateUsLong" :
        case "max_date_us_long" :
          validator = {
            condition: "<=",
            dateType: "US_LONG",
            params: [ruleParams],
            pattern: /^(0[1-9]|1[012])[-\/\.](0[1-9]|[12][0-9]|3[01])[-\/\.](19|20)\d\d$/,
            message: "INVALID_DATE_US_LONG_MAX",
            type: "conditionalDate"
          };
          break;
        case "dateUsLongMin" :
        case "date_us_long_min" :
        case "minDateUsLong" :
        case "min_date_us_long" :
          validator = {
            condition: ">=",
            dateType: "US_LONG",
            params: [ruleParams],
            pattern: /^(0[1-9]|1[012])[-\/\.](0[1-9]|[12][0-9]|3[01])[-\/\.](19|20)\d\d$/,
            message: "INVALID_DATE_US_LONG_MIN",
            type: "conditionalDate"
          };
          break;
        case "dateUsShort" :
        case "date_us_short" :
          validator = {
            pattern: /^(0[1-9]|1[012])[-\/\.](0[1-9]|[12][0-9]|3[01])[-\/\.]\d\d$/,
            message: "INVALID_DATE_US_SHORT",
            type: "regex"
          };
          break;
        case "dateUsShortBetween" :
        case "date_us_short_between" :
        case "betweenDateUsShort" :
        case "between_date_us_short" :
          var ranges = ruleParams.split(',');
          if (ranges.length !== 2) {
            throw "This validation must include exactly 2 params separated by a comma (,) ex.: between_date_us_short:01/01/90,12/31/15";
          }
          validator = {
            condition: [">=","<="],
            dateType: "US_SHORT",
            params: [ranges[0], ranges[1]],
            pattern: /^(0[1-9]|1[012])[-\/\.](0[1-9]|[12][0-9]|3[01])[-\/\.]\d\d$/,
            message: "INVALID_DATE_US_SHORT_BETWEEN",
            type: "conditionalDate"
          };
          break;
        case "dateUsShortMax" :
        case "date_us_short_max" :
        case "maxDateUsShort" :
        case "max_date_us_short" :
          validator = {
            condition: "<=",
            dateType: "US_SHORT",
            params: [ruleParams],
            pattern: /^(0[1-9]|1[012])[-\/\.](0[1-9]|[12][0-9]|3[01])[-\/\.]\d\d$/,
            message: "INVALID_DATE_US_SHORT_MAX",
            type: "conditionalDate"
          };
          break;
        case "dateUsShortMin" :
        case "date_us_short_min" :
        case "minDateUsShort" :
        case "min_date_us_short" :
          validator = {
            condition: ">=",
            dateType: "US_SHORT",
            params: [ruleParams],
            pattern: /^(0[1-9]|1[012])[-\/\.](0[1-9]|[12][0-9]|3[01])[-\/\.]\d\d$/,
            message: "INVALID_DATE_US_SHORT_MIN",
            type: "conditionalDate"
          };
          break;
        case "different" :
        case "differentInput" :
        case "different_input" :
          var args = ruleParams.split(',');
          validator = {
            condition: "!=",
            message: "INVALID_INPUT_DIFFERENT",
            params: args,
            type: "matching"
          };
          break;
        case "digits" :
          validator = {
            pattern: "^\\d{" + ruleParams + "}$",
            message: "INVALID_DIGITS",
            params: [ruleParams],
            type: "regex"
          };
          break;
        case "digitsBetween" :
        case "digits_between" :
          var ranges = ruleParams.split(',');
          if (ranges.length !== 2) {
            throw "This validation must include exactly 2 params separated by a comma (,) ex.: digits_between:1,5";
          }
          validator = {
            pattern: "^\\d{" + ranges[0] + "," + ranges[1] + "}$",
            message: "INVALID_DIGITS_BETWEEN",
            params: [ranges[0], ranges[1]],
            type: "regex"
          };
          break;
        case "email" :
          validator = {
            // Email RFC 5322, pattern pulled from  http://www.regular-expressions.info/email.html
            // but removed necessity of a TLD (Top Level Domain) which makes this email valid: admin@mailserver1
            pattern: /^[-\wа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ0-9#~!$%^&*_=+\/`\|}{\'?]+(\.[-\wа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ0-9#~!$%^&*_=+\/`\|}{\'?]+)*@([\wа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ0-9_][-\wа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ0-9_]*(\.[-\wа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ0-9_]+)*([\wа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ]+)|(\.[\wа-яàáâãäåæçèéêëœìíïîðòóôõöøùúûñüýÿßÞďđ]{2,6})|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i,
            message: "INVALID_EMAIL",
            type: "regex"
          };
          break;
        case "exactLen" :
        case "exact_len" :
          validator = {
            pattern: "^(.|[\\r\\n]){" + ruleParams + "}$",
            message: "INVALID_EXACT_LEN",
            params: [ruleParams],
            type: "regex"
          };
          break;
        case "float" :
          validator = {
            pattern: /^\d*\.{1}\d+$/,
            message: "INVALID_FLOAT",
            type: "regex"
          };
          break;
        case "floatSigned" :
        case "float_signed" :
          validator = {
            pattern: /^[-+]?\d*\.{1}\d+$/,
            message: "INVALID_FLOAT_SIGNED",
            type: "regex"
          };
          break;
        case "iban" :
          validator = {
            pattern: /^[a-zA-Z]{2}\d{2}\s?([0-9a-zA-Z]{4}\s?){4}[0-9a-zA-Z]{2}$/i,
            message: "INVALID_IBAN",
            type: "regex"
          };
          break;
        case "in" :
        case "inList" :
        case "in_list" :
          var list = ruleParams.replace(/,/g, '|'); // replace ',' by '|'
          validator = {
            pattern: "^(\\b(" + list + ")\\b)$",
            message: "INVALID_IN_LIST",
            params: [ruleParams],
            type: "regex"
          };
          break;
        case "int" :
        case "integer" :
          validator = {
            pattern: /^\d+$/,
            message: "INVALID_INTEGER",
            type: "regex"
          };
          break;
        case "intSigned" :
        case "integerSigned" :
        case "int_signed" :
        case "integer_signed" :
          validator = {
            pattern: /^[+-]?\d+$/,
            message: "INVALID_INTEGER_SIGNED",
            type: "regex"
          };
          break;
        case "ip" :
        case "ipv4" :
          validator = {
            pattern: /^(25[0-5]|2[0-4]\d|[0-1]?\d?\d)(\.(25[0-5]|2[0-4]\d|[0-1]?\d?\d)){3}$/,
            message: "INVALID_IPV4",
            type: "regex"
          };
          break;
        case "ipv6" :
          validator = {
            pattern: /^(?:[0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$/i,
            message: "INVALID_IPV6",
            type: "regex"
          };
          break;
        case "match" :
        case "matchInput" :
        case "match_input" :
        case "same" :
          var args = ruleParams.split(',');
          validator = {
            condition: "===",
            message: "INVALID_INPUT_MATCH",
            params: args,
            type: "matching"
          };
          break;
        case "max" :
          validator = {
            patternLength: "^(.|[\\r\\n]){0," + ruleParams + "}$",
            messageLength: "INVALID_MAX_CHAR",
            conditionNum: "<=",
            messageNum: "INVALID_MAX_NUM",
            params: [ruleParams],
            type: "autoDetect"
          };
          break;
        case "maxLen" :
        case "max_len" :
          validator = {
            pattern: "^(.|[\\r\\n]){0," + ruleParams + "}$",
            message: "INVALID_MAX_CHAR",
            params: [ruleParams],
            type: "regex"
          };
          break;
        case "maxNum" :
        case "max_num" :
          validator = {
            condition: "<=",
            message: "INVALID_MAX_NUM",
            params: [ruleParams],
            type: "conditionalNumber"
          };
          break;
        case "min" :
          validator = {
            patternLength: "^(.|[\\r\\n]){" + ruleParams + ",}$",
            messageLength: "INVALID_MIN_CHAR",
            conditionNum: ">=",
            messageNum: "INVALID_MIN_NUM",
            params: [ruleParams],
            type: "autoDetect"
          };
          break;
        case "minLen" :
        case "min_len" :
          validator = {
            pattern: "^(.|[\\r\\n]){" + ruleParams + ",}$",
            message: "INVALID_MIN_CHAR",
            params: [ruleParams],
            type: "regex"
          };
          break;
        case "minNum" :
        case "min_num" :
          validator = {
            condition: ">=",
            message: "INVALID_MIN_NUM",
            params: [ruleParams],
            type: "conditionalNumber"
          };
          break;
        case "notIn" :
        case "not_in" :
        case "notInList" :
        case "not_in_list" :
          var list = ruleParams.replace(/,/g, '|'); // replace ',' by '|'
          validator = {
            pattern: "^((?!\\b(" + list + ")\\b).)+$",
            message: "INVALID_NOT_IN_LIST",
            params: [ruleParams],
            type: "regex"
          };
          break;
        case "numeric" :
          validator = {
            pattern: /^\d*\.?\d+$/,
            message: "INVALID_NUMERIC",
            type: "regex"
          };
          break;
        case "numericSigned" :
        case "numeric_signed" :
          validator = {
            pattern: /^[-+]?\d*\.?\d+$/,
            message: "INVALID_NUMERIC_SIGNED",
            type: "regex"
          };
          break;
        case "pattern" :
        case "regex" :
          // Custom User Regex is a special case, the properties (message, pattern) were created and dealt separately prior to the for loop
          validator = {
            pattern: customUserRegEx.pattern,
            message: "INVALID_PATTERN",
            params: [customUserRegEx.message],
            type: "regex"
          };
          break;
        case "remote" :
          validator = {
            message: '', // there is no error message defined on this one since user will provide his own error message via remote response or `alt=`
            params: [ruleParams],
            type: "remote"
          };
          break;
        case "required" :
          validator = {
            pattern: /\S+/,
            message: "INVALID_REQUIRED",
            type: "regex"
          };
          break;
        case "size" :
          validator = {
            patternLength: "^(.|[\\r\\n]){" + ruleParams + "}$",
            messageLength: "INVALID_EXACT_LEN",
            conditionNum: "==",
            messageNum: "INVALID_EXACT_NUM",
            params: [ruleParams],
            type: "autoDetect"
          };
          break;
        case "url" :
          validator = {
            pattern: /^(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?/i,
            message: "INVALID_URL",
            type: "regex"
          };
          break;
        case "time" :
          validator = {
            pattern: /^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/,
            message: "INVALID_TIME",
            type: "regex"
          };
          break;
      } // switch()

      // add the possible alternate text user might have provided
      validator.altText = alternateText;

      return validator;
    } // getElementValidators()
}]);

/**
 * Angular-Validation Service (ghiscoding)
 * https://github.com/ghiscoding/angular-validation
 *
 * @author: Ghislain B.
 * @desc: angular-validation service definition
 * Provide a way to programmatically create validators and validate a form directly from within the controller.
 * This Service is totally independant from the Directive, it could be used separately but the minimum it needs is the `validation-rules.js` file.
 */
angular
  .module('ghiscoding.validation')
  .service('validationService', ['$interpolate', '$timeout', 'validationCommon', function ($interpolate, $timeout, validationCommon) {
    // global variables of our object (start with _var)
    var _blurHandler;
    var _watchers = [];

    // service constructor
    var validationService = function (globalOptions) {
      this.isValidationCancelled = false;       // is the validation cancelled?
      this.timer = null;                        // timer of user inactivity time
      this.validationAttrs = {};                // Current Validator attributes
      this.commonObj = new validationCommon();  // Object of validationCommon service

      // if global options were passed to the constructor
      if (!!globalOptions) {
        this.setGlobalOptions(globalOptions);
      }
    }

    // list of available published public functions of this object
    validationService.prototype.addValidator = addValidator;                                          // add a Validator to current element
    validationService.prototype.checkFormValidity = checkFormValidity;                                // check the form validity (can be called by an empty validationService and used by both Directive/Service)
    validationService.prototype.removeValidator = removeValidator;                                    // remove a Validator from an element
    validationService.prototype.resetForm = resetForm;                                                // reset the form (reset it to Pristine and Untouched)
    validationService.prototype.setDisplayOnlyLastErrorMsg = setDisplayOnlyLastErrorMsg;              // setter on the behaviour of displaying only the last error message
    validationService.prototype.setGlobalOptions = setGlobalOptions;                                  // set and initialize global options used by all validators
    validationService.prototype.clearInvalidValidatorsInSummary = clearInvalidValidatorsInSummary;    // clear clearInvalidValidatorsInSummary

    return validationService;

    //----
    // Public Functions declaration
    //----------------------------------

    /** Add a validator on a form element, the argument could be passed as 1 single object (having all properties) or 2 to 3 string arguments
     * @param mixed var1: could be a string (element name) or an object representing the validator
     * @param string var2: [optional] validator rules
     * @param string var3: [optional] friendly name
     */
    function addValidator(var1, var2, var3) {
      var self = this;
      var attrs = {};

      // find if user provided 2 string arguments else it will be a single object with all properties
      if(typeof var1 === "string" && typeof var2 === "string") {
        attrs.elmName = var1;
        attrs.rules = var2;
        attrs.friendlyName = (typeof var3 === "string") ? var3 : '';
      }else {
        attrs = var1;
      }

      // Make sure that we have all required attributes to work properly
      if(typeof attrs !== "object" || !attrs.hasOwnProperty('elmName') || !attrs.hasOwnProperty('rules') || (!attrs.hasOwnProperty('scope') && typeof self.validationAttrs.scope === "undefined") ) {
        throw 'Angular-Validation-Service requires at least the following 3 attributes: {elmName, rules, scope}';
      }

      // get the scope from the validator or from the global options (validationAttrs)
      var scope = (!!attrs.scope) ? attrs.scope : self.validationAttrs.scope;

      // find the DOM element & make sure it's a filled object before going further
      // we will exclude disabled/ng-disabled element from being validated
      attrs.elm = angular.element(document.querySelector('[name="'+attrs.elmName+'"]'));
      if(typeof attrs.elm !== "object" || attrs.elm.length === 0) {
        return self;
      }

      // copy the element attributes name to use throughout validationCommon
      // when using dynamic elements, we might have encounter unparsed or uncompiled data, we need to get Angular result with $interpolate
      if(new RegExp("{{(.*?)}}").test(attrs.elmName)) {
        attrs.elmName = $interpolate(attrs.elmName)(scope);
      }
      attrs.name = attrs.elmName;

      // user could pass his own scope, useful in a case of an isolate scope
      if (!!self.validationAttrs.isolatedScope) {
        var tempValidationOptions = scope.$validationOptions || null; // keep global validationOptions
        scope = self.validationAttrs.isolatedScope;                                  // rewrite original scope
        if(!!tempValidationOptions) {
          scope.$validationOptions = tempValidationOptions;           // reuse the validationOption from original scope
        }
      }

      // onBlur make validation without waiting
      attrs.elm.bind('blur', _blurHandler = function(event) {
        // get the form element custom object and use it after
        var formElmObj = self.commonObj.getFormElementByName(attrs.elmName);

        if (!!formElmObj && !formElmObj.isValidationCancelled) {
          // re-initialize to use current element & validate without delay
          self.commonObj.initialize(scope, attrs.elm, attrs, attrs.ctrl);
          attemptToValidate(self, event.target.value, 10);
        }
      });

      // merge both attributes but 2nd object (attrs) as higher priority, so that for example debounce property inside `attrs` as higher priority over `validatorAttrs`
      // so the position inside the mergeObject call is very important
      attrs = self.commonObj.mergeObjects(self.validationAttrs, attrs);

      // watch the `disabled` attribute for changes
      // if it become disabled then skip validation else it becomes enable then we need to revalidate it
      watchNgDisabled(self, scope, attrs);

      // if DOM element gets destroyed, we need to cancel validation, unbind onBlur & remove it from $validationSummary
      attrs.elm.on('$destroy', function() {
        // get the form element custom object and use it after
        var formElmObj = self.commonObj.getFormElementByName(self.commonObj.ctrl.$name);

        // unbind everything and cancel the validation
        if(!!formElmObj) {
          cancelValidation(self, formElmObj);
          self.commonObj.removeFromValidationSummary(attrs.name);
        }
      });

      // watch the element for any value change, validate it once that happen
      var watcherHandler = scope.$watch(attrs.elmName, function (newVal, oldVal) {
        // when previous value was set and new value is not, this is most probably an invalid character entered in a type input="text"
        // we will still call the `.validate()` function so that it shows also the possible other error messages
        if(newVal === undefined && oldVal !== undefined) {
          $timeout.cancel(self.timer);
          self.commonObj.ctrl.$setValidity('validation', self.commonObj.validate('', true));
          return;
        }
        // from the DOM element, find the Angular controller of this element & add value as well to list of attribtues
        attrs.ctrl = angular.element(attrs.elm).controller('ngModel');
        attrs.value = newVal;

        self.commonObj.initialize(scope, attrs.elm, attrs, attrs.ctrl);
        attemptToValidate(self, newVal);
      }, true); // $watch()

      // save the watcher inside an array in case we want to deregister it when removing a validator
      _watchers.push({ elmName: attrs.elmName, watcherHandler: watcherHandler});

      return self;
    } // addValidator()

    /** Check the form validity (can be called by an empty validationService and used by both Directive/Service)
     * Loop through Validation Summary and if any errors found then display them and return false on current function
     * @param object Angular Form or Scope Object
     * @return bool isFormValid
     */
    function checkFormValidity(obj) {
      var self = this;
      var ctrl, elm, elmName = '', isValid = true;
      if(typeof obj === "undefined" || typeof obj.$validationSummary === "undefined") {
        throw 'checkFormValidity() requires a valid Angular Form or $scope/vm object passed as argument to work properly, for example:: fn($scope) OR fn($scope.form1) OR fn(vm) OR fn(vm.form1)';
      }

      // loop through $validationSummary and display errors when found on each field
      for(var i = 0, ln = obj.$validationSummary.length; i < ln; i++) {
        isValid = false;
        elmName = obj.$validationSummary[i].field;

        if(!!elmName) {
          // get the form element custom object and use it after
          var formElmObj = self.commonObj.getFormElementByName(elmName);

          if(!!formElmObj && !!formElmObj.elm && formElmObj.elm.length > 0) {
            // make the element as it was touched for CSS, only works in AngularJS 1.3+
            if (typeof formElmObj.ctrl.$setTouched === "function") {
              formElmObj.ctrl.$setTouched();
            }
            self.commonObj.updateErrorMsg(obj.$validationSummary[i].message, { isSubmitted: true, isValid: formElmObj.isValid, obj: formElmObj });
          }
        }
      }
      return isValid;
    }

    /** Remove all objects in validationsummary and matching objects in FormElementList.
     * This is for use in a wizard type setting, where you 'move back' to a previous page in wizard.
     * In this case you need to remove invalid validators that will exist in 'the future'.
     * @param object Angular Form or Scope Object
     */
    function clearInvalidValidatorsInSummary(obj) {
      var self = this;
      if (typeof obj === "undefined" || typeof obj.$validationSummary === "undefined") {
        throw 'clearInvalidValidatorsInSummary() requires a valid Angular Form or $scope/vm object passed as argument to work properly, for example:: fn($scope) OR fn($scope.form1) OR fn(vm) OR fn(vm.form1)';
      }
      // Get list of names to remove
      var elmName = [];
      for (var i = 0, ln = obj.$validationSummary.length; i < ln; i++) {
        elmName.push(obj.$validationSummary[i].field);
      }
      // Loop on list of names. Cannot loop on obj.$validationSummary as you are removing objects from it in the loop.
      for (i = 0, ln = elmName.length; i < ln; i++) {
        if (!!elmName[i]) {
          self.commonObj.removeFromFormElementObjectList(elmName[i]);
          self.commonObj.removeFromValidationSummary(elmName[i], obj.$validationSummary);
        }
      }
    }

    /** Remove a validator and also any withstanding error message from that element
     * @param object Angular Form or Scope Object
     * @param array/string of element name(s) (name attribute)
     * @return object self
     */
    function removeValidator(obj, args) {
      var self = this;
      var formElmObj;

      if(typeof obj === "undefined" || typeof obj.$validationSummary === "undefined") {
        throw 'removeValidator() only works with Validation that were defined by the Service (not by the Directive) and requires a valid Angular Form or $scope/vm object passed as argument to work properly, for example:: fn($scope) OR fn($scope.form1) OR fn(vm) OR fn(vm.form1)';
      }

      // Note: removeAttr() will remove validation attribute from the DOM (if defined by Directive), but as no effect when defined by the Service
      // removeValidator() 2nd argument could be passed an Array or a string of element name(s)
      //   if it's an Array we will loop through all elements to be removed
      //   else just remove the 1 element defined as a string
      if (args instanceof Array) {
        for (var i = 0, ln = args.length; i < ln; i++) {
          formElmObj = self.commonObj.getFormElementByName(args[i]);
          formElmObj.elm.removeAttr('validation');
          removeWatcherAndErrorMessage(self, formElmObj, obj.$validationSummary);
        }
      }
      else if(args instanceof Object && !!args.formElmObj) {
        formElmObj = args.formElmObj;
        formElmObj.elm.removeAttr('validation');
        removeWatcherAndErrorMessage(args.self, formElmObj, obj.$validationSummary);
      }
      else {
        formElmObj = self.commonObj.getFormElementByName(args);
        formElmObj.elm.removeAttr('validation');
        removeWatcherAndErrorMessage(self, formElmObj, obj.$validationSummary);
      }

      return self;
    }

    /** Reset a Form, reset all input element to Pristine, Untouched & remove error dislayed (if any)
     * @param object Angular Form or Scope Object
     * @param bool empty also the element values? (True by default)
     */
    function resetForm(obj, args) {
      var self = this;
      var formElmObj;
      var args = args || {};
      var shouldRemoveValidator = (typeof args.removeAllValidators !== "undefined") ? args.removeAllValidators : false;
      var shouldEmptyValues = (typeof args.emptyAllInputValues !== "undefined") ? args.emptyAllInputValues : false;

      if(typeof obj === "undefined" || typeof obj.$name === "undefined") {
        throw 'resetForm() requires a valid Angular Form object passed as argument to work properly (ex.: $scope.form1).';
      }

      // get all Form input elements and loop through all of them to set them Pristine, Untouched and also remove errors displayed
      var formElements = self.commonObj.getFormElements(obj.$name);
      if(formElements instanceof Array) {
        for (var i = 0, ln = formElements.length; i < ln; i++) {
          formElmObj = formElements[i];

          // should we empty input elment values as well?
          if(!!shouldEmptyValues) {
            formElmObj.elm.val(null);
          }

          // should we remove all validators?
          // if yes, then run removeValidator() and since that already removes message & make input valid, no need to run the $setUntouched() and $setPristine()
          // else make the field $setUntouched() and $setPristine()
          if(!!shouldRemoveValidator) {
            removeValidator(obj, { self: self, formElmObj: formElmObj});
          }else {
            // make the element as it was touched for CSS, only works in AngularJS 1.3+
            if (typeof formElmObj.ctrl.$setUntouched === "function") {
              formElmObj.ctrl.$setUntouched();
            }
            formElmObj.ctrl.$setPristine();
            self.commonObj.updateErrorMsg('', { isValid: false, obj: formElmObj });
          }
        }
      }
    }

    /** Setter on the behaviour of displaying only the last error message of each element.
     * By default this is false, so the behavior is to display all error messages of each element.
     * @param boolean value
     */
    function setDisplayOnlyLastErrorMsg(boolValue) {
      var self = this;
      var isDisplaying = (typeof boolValue === "boolean") ? boolValue : true;
      self.commonObj.setDisplayOnlyLastErrorMsg(isDisplaying);
    }

    /** Set and initialize global options used by all validators
     * @param object attrs: global options
     * @return object self
     */
    function setGlobalOptions(options) {
      var self = this;
      self.validationAttrs = options; // save in global
      self.commonObj.setGlobalOptions(options);

      return self;
    }

    //----
    // Private functions declaration
    //----------------------------------

    /** Validator function to attach to the element, this will get call whenever the input field is updated
     *  and is also customizable through the (typing-limit) for which inactivity this.timer will trigger validation.
     * @param object self
     * @param string value: value of the input field
     */
    function attemptToValidate(self, value, typingLimit) {
      // get the waiting delay time if passed as argument or get it from common Object
      var waitingLimit = (typeof typingLimit !== "undefined") ? typingLimit : self.commonObj.typingLimit;

      // get the form element custom object and use it after
      var formElmObj = self.commonObj.getFormElementByName(self.commonObj.ctrl.$name);

      // pre-validate without any events just to pre-fill our validationSummary with all field errors
      // passing false as 2nd argument for not showing any errors on screen
      self.commonObj.validate(value, false);

      // if field is not required and his value is empty, cancel validation and exit out
      if(!self.commonObj.isFieldRequired() && (value === "" || value === null || typeof value === "undefined")) {
        cancelValidation(self, formElmObj);
        return value;
      }else {
        formElmObj.isValidationCancelled = false;
      }

      // invalidate field before doing any validation
      if(self.commonObj.isFieldRequired() || !!value) {
        self.commonObj.ctrl.$setValidity('validation', false);
      }

      // if a field holds invalid characters which are not numbers inside an `input type="number"`, then it's automatically invalid
      // we will still call the `.validate()` function so that it shows also the possible other error messages
      if((value === "" || typeof value === "undefined") && self.commonObj.elm.prop('type').toUpperCase() === "NUMBER") {
        $timeout.cancel(self.timer);
        self.commonObj.ctrl.$setValidity('validation', self.commonObj.validate(value, true));
        return value;
      }

      // select(options) will be validated on the spot
      if(self.commonObj.elm.prop('tagName').toUpperCase() === "SELECT") {
        self.commonObj.ctrl.$setValidity('validation', self.commonObj.validate(value, true));
        return value;
      }

      // onKeyDown event is the default of Angular, no need to even bind it, it will fall under here anyway
      // in case the field is already pre-filled, we need to validate it without looking at the event binding
      if(typeof value !== "undefined") {
        // Make the validation only after the user has stopped activity on a field
        // everytime a new character is typed, it will cancel/restart the timer & we'll erase any error mmsg
        self.commonObj.updateErrorMsg('');
        $timeout.cancel(self.timer);
        self.timer = $timeout(function() {
          self.commonObj.scope.$evalAsync(self.commonObj.ctrl.$setValidity('validation', self.commonObj.validate(value, true) ));
        }, waitingLimit);
      }

      return value;
    } // attemptToValidate()

    /** Cancel current validation test and blank any leftover error message
     * @param object obj
     */
    function cancelValidation(obj, formElmObj) {
      // get the form element custom object and use it after
      var ctrl = (!!formElmObj && !!formElmObj.ctrl) ? formElmObj.ctrl : obj.commonObj.ctrl;

      if(!!formElmObj) {
        formElmObj.isValidationCancelled = true;
      }
      $timeout.cancel(self.timer);
      ctrl.$setValidity('validation', true);
      obj.commonObj.updateErrorMsg('', { isValid: true, obj: formElmObj });

      // unbind onBlur handler (if found) so that it does not fail on a non-required element that is now dirty & empty
      if(typeof _blurHandler === "function") {
        var elm = (!!formElmObj && !!formElmObj.elm) ? formElmObj.elm : obj.commonObj.elm;
        elm.unbind('blur', _blurHandler);
      }
    }

    /** Remove a watcher and any withstanding error message from the element
     * @param object self
     * @param object formElmObj: form element object
     * @param object validationSummary
     */
    function removeWatcherAndErrorMessage(self, formElmObj, validationSummary) {
      var scope =
        !!self.commonObj.scope
          ? self.commonObj.scope
          : !!formElmObj.scope
            ? formElmObj.scope
            : null;
      if(typeof scope === "undefined") {
        throw 'removeValidator() requires a valid $scope object passed but unfortunately could not find it.';
      }

      // deregister the $watch from the _watchers array we kept it
      var foundWatcher = self.commonObj.arrayFindObject(_watchers, 'elmName', formElmObj.fieldName);
      if(!!foundWatcher) {
        foundWatcher.watcherHandler(); // deregister the watch by calling his handler
      }

      // make the validation cancelled so it won't get called anymore in the blur eventHandler
      formElmObj.isValidationCancelled = true;
      formElmObj.isValid = true;
      formElmObj.attrs.validation = "";
      cancelValidation(self, formElmObj);

      // now to remove any errors, we need to make the element untouched, pristine and remove the validation
      // also remove it from the validationSummary list and remove any displayed error
      if (typeof formElmObj.ctrl.$setUntouched === "function") {
        // make the element untouched in CSS, only works in AngularJS 1.3+
        formElmObj.ctrl.$setUntouched();
      }
      self.commonObj.scope = scope;
      formElmObj.ctrl.$setPristine();
      self.commonObj.removeFromValidationSummary(formElmObj.fieldName, validationSummary);
    }

    function watchNgDisabled(self, scope, attrs) {
      scope.$watch(function() {
        return (typeof attrs.elm.attr('ng-disabled') === "undefined") ? null : scope.$eval(attrs.elm.attr('ng-disabled')); //this will evaluate attribute value `{{}}``
      }, function(disabled) {
        if(typeof disabled === "undefined" || disabled === null) {
          return null;
        }

        // get current ctrl of element & re-initialize to use current element
        attrs.ctrl = angular.element(attrs.elm).controller('ngModel');
        self.commonObj.initialize(scope, attrs.elm, attrs, attrs.ctrl);

        // get the form element custom object and use it after
        var formElmObj = self.commonObj.getFormElementByName(attrs.name);

        // use a timeout so that the digest of removing the `disabled` attribute on the DOM is completed
        // because commonObj.validate() checks for both the `disabled` and `ng-disabled` attribute so it basically fails without the $timeout because of the digest
        $timeout(function() {
          if (disabled) {
            // Remove it from validation summary
            attrs.ctrl.$setValidity('validation', true);
            self.commonObj.updateErrorMsg('', { isValid: true, obj: formElmObj });
            self.commonObj.removeFromValidationSummary(attrs.name);
          } else {
            // Re-Validate the input when enabled (without displaying the error)
            var value = attrs.ctrl.$viewValue || '';

            // re-initialize to use current element & validate without delay (without displaying the error)
            self.commonObj.initialize(scope, attrs.elm, attrs, attrs.ctrl);
            attrs.ctrl.$setValidity('validation', self.commonObj.validate(value, false));

            // make sure it's re-enable the validation as well
            if(!!formElmObj) {
              formElmObj.isValidationCancelled = false;
            }

            // re-attach the onBlur handler
            attrs.elm.bind('blur', _blurHandler = function(event) {
              if (!!formElmObj && !formElmObj.isValidationCancelled) {
                attemptToValidate(self, event.target.value, 10);
              }
            });
          }
        }, 0, false);

        // these cannot be done inside the $timeout, when doing it would cancel validation for all element because of the delay
        if (disabled) {
          // Turn off validation when element is disabled & remove from validationSummary (seems I need to remove from summary inside $timeout and outside)
          // make the element as it was untouched for CSS, only works in AngularJS 1.3+
          if (typeof attrs.ctrl.$setUntouched === "function") {
            attrs.ctrl.$setUntouched();
          }
          attrs.ctrl.$setValidity('validation', true);
          self.commonObj.removeFromValidationSummary(attrs.name);
        }
      });
    }

}]); // validationService
