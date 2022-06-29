@extends('spark::layouts.app')
@section('content')
<home :user="user" inline-template>
  <div class="container">
      <!-- Application Dashboard -->
      <div class="row justify-content-center">
        <div class="col-md-12">
          <form method="post" id="form-store-submit" onsubmit="return validateForm()" action="{{ route('sites.savestore') }}">
            {{ csrf_field() }}
            <table>
              <tr><td>Add your store to WpDrift.io</td></tr>
              <tr><td>What's the name of your company?</td></tr>
              <tr>
                <td>
                  @if($companies_store_count > 0)
                      <select name="company-id" id="company-id">
                      @foreach($companies as $company)
                          <option value="{{ $company->id }}">{{ $company->name }}</option>
                      @endforeach
                      @if($companies_store_count < 21)
                        <option value="add-company">Add Company +</option>
                      @endif
                      </select>
                    @if($companies_store_count < 21)
                    <div id="add-company-div" style="display: none;">
                      <input placeholder="Company Name" size="large" rows="2" validateevent="true" class="el-input__inner" id="company-name" name="company-name" type="text">
                    </div>
                    @else
                    <tr><td>You are having more than <strong>20 Companies</strong>, To add more contact Admin.</td></tr>
                    @endif
                  @else
                      <input placeholder="Company Name" size="large" rows="2" validateevent="true" class="el-input__inner" id="company-name" name="company-name" type="text">
                  @endif
                </td>
              </tr>
              <tr><td>Are you sure you want to create a new company? It will have its own subscription and billing.</td></tr>
              <tr><td>What's your store's website?</td></tr>
              @if($stores_count >= 21)
              <tr><td>You are having more than <strong>20 Sites</strong>, To add more contact Admin.</td></tr>
              @else
              <tr><td><input placeholder="mystore.com" size="large" rows="2" validateevent="true" class="el-input__inner" id="store-url" name="store-url" type="text"></td></tr>
              <tr><td><input placeholder="Store Name" size="large" rows="2" validateevent="true" class="el-input__inner" id="store-name" name="store-name" type="text"></td></tr>
              <tr><td><button type="submit" id="add-store-btn" class="el-button add-store-button el-button--primary el-button--large"><!----><!----><span>
                      Add Store
                  </span></button></td></tr>
              @endif
            </table>
          </form>
        </div>
      </div><!-- .justify-content-center -->
    </div><!-- .container -->
  </home>
  @endsection
@section('javascript')
<script type="text/javascript">
  // Validate form for submission
  var frmSbmtStore = document.getElementById("form-store-submit");
  var compDD = document.getElementById('company-id');
  function validateForm() {
    if(compDD.value == 'add-company') {
      // validate the input box for add company
      if(document.getElementById('company-name').value == '') {
        alert('Please fill Company Name');
        return false;
      }
    }
    if(document.getElementById('store-url').value == '') {
      alert('Please fill Store URL');
      return false;
    }
    if(document.getElementById('store-name').value == '') {
      alert('Please fill Store Name');
      return false;
    }
    return true;
  }
   compDD.onchange = function(){
     if(this.value == "add-company") {
       document.getElementById('add-company-div').style.display = "block";
     } else {
       document.getElementById('add-company-div').style.display = "none";
     }
   };
</script>
@endsection
