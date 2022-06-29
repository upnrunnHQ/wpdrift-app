<form role="form">
    @if (Spark::usesTeams() && Spark::onlyTeamPlans())
        <!-- Team Name -->
        <div class="form-group row" v-if=" ! invitation">
            <div class="col-md-12">
                <input type="text" placeholder="{{ __('teams.team_name') }}" class="form-control" name="team" v-model="registerForm.team" :class="{'is-invalid': registerForm.errors.has('team')}" autofocus>

                <span class="invalid-feedback" v-show="registerForm.errors.has('team')">
                    @{{ registerForm.errors.get('team') }}
                </span>
            </div>
        </div>

        @if (Spark::teamsIdentifiedByPath())
            <!-- Team Slug (Only Shown When Using Paths For Teams) -->
            <div class="form-group row" v-if=" ! invitation">
                <div class="col-md-12">
                    <input type="text" class="form-control" placeholder="{{ __('teams.team_slug') }}" name="team_slug" v-model="registerForm.team_slug" :class="{'is-invalid': registerForm.errors.has('team_slug')}" autofocus>

                    <small class="form-text text-muted" v-show="! registerForm.errors.has('team_slug')">
                        {{__('teams.slug_input_explanation')}}
                    </small>

                    <span class="invalid-feedback" v-show="registerForm.errors.has('team_slug')">
                        @{{ registerForm.errors.get('team_slug') }}
                    </span>
                </div>
            </div>
        @endif
    @endif

    <!-- Name -->
    <div class="form-group row">
        <div class="col-md-12">
            <input type="text" placeholder="{{__('Name')}}" class="form-control" name="name" v-model="registerForm.name" :class="{'is-invalid': registerForm.errors.has('name')}" autofocus>

            <span class="invalid-feedback" v-show="registerForm.errors.has('name')">
                @{{ registerForm.errors.get('name') }}
            </span>
        </div>
    </div>

    <!-- E-Mail Address -->
    <div class="form-group row">
        <div class="col-md-12">
            <input type="email" placeholder="{{__('E-Mail Address')}}" class="form-control" name="email" v-model="registerForm.email" :class="{'is-invalid': registerForm.errors.has('email')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('email')">
                @{{ registerForm.errors.get('email') }}
            </span>
        </div>
    </div>

    <!-- Password -->
    <div class="form-group row">
        <div class="col-md-12">
            <input type="password" placeholder="{{__('Password')}}" class="form-control" name="password" v-model="registerForm.password" :class="{'is-invalid': registerForm.errors.has('password')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('password')">
                @{{ registerForm.errors.get('password') }}
            </span>
        </div>
    </div>

    <!-- Terms And Conditions -->
    <div v-if=" ! selectedPlan || selectedPlan.price == 0">
        <div class="form-group row" :class="{'is-invalid': registerForm.errors.has('terms')}">
            <div class="col-md-12">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" v-model="registerForm.terms">
                        {!! __('By registering you accept the :linktosOpenToS:linktosClose and :linkppOpenPP:linkppClose.', ['linktosOpen' => '<a href="https://wpdrift.io/tos/" target="_blank">', 'linktosClose' => '</a>', 'linkppOpen' => '<a href="https://wpdrift.io/privacy/" target="_blank">', 'linkppClose' => '</a>']) !!}
                    </label>
                    <span class="invalid-feedback" v-show="registerForm.errors.has('terms')">
                        <strong>@{{ registerForm.errors.get('terms') }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-12">
                <button class="btn btn-primary" @click.prevent="register" :disabled="registerForm.busy">
                    <span v-if="registerForm.busy">
                        <i class="fa fa-btn fa-spinner fa-spin"></i> {{__('Registering')}}
                    </span>

                    <span v-else>
                        <i class="fa fa-btn fa-check-circle"></i> {{__('Register')}}
                    </span>
                </button>
            </div>
        </div>
    </div>
</form>
