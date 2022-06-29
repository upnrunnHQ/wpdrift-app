<div class="card card-default">
    <div class="card-header">
        {{__('Update Details')}}
    </div>
    <div class="card-body">
        <form method="post" action="{{ route('companies.update',[$company->id]) }}">
          {{ csrf_field() }}
          <input type="hidden" name="_method" value="put">
          <div class="form-group row">
            <label class="col-md-4 col-form-label text-md-right" for="company-name">Name<span class="required text-danger">*</span></label>
            <div class="col-md-6">
              <input placeholder="Enter name"
                   id="company-name"
                   required
                   name="name"
                   spellcheck="false"
                   class="form-control"
                   value="{{ $company->name }}"
              />
              {!! $errors->first('name', '<small class="text-danger">:message</small>') !!}
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-4 col-form-label text-md-right" for="company-content">Description</label>
            <div class="col-md-6">
            <textarea placeholder="Enter description"
                  style="resize: vertical"
                  id="company-content"
                  name="description"
                  rows="5" spellcheck="false"
                  class="form-control autosize-target text-left">
                  {{ $company->description }}</textarea>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-4"></div>
            <div class="col-md-6">
              <input type="submit" class="btn btn-primary"
                value="Submit"/>
            </div>
          </div>
        </form>
    </div>
</div>
