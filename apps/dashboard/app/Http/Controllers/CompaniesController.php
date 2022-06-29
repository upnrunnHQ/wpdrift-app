<?php
// Setting up the Companies for User that will top of store
// App/Http/Controllers/CompaniesController.php
namespace App\Http\Controllers;

use App\Company;
use App\CompanyUser;
use App\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\UserDefaultStore;

class CompaniesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Custom created middleware for checking user is from company for view and edit pages.
        $this->middleware('companyUser');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Listing out the companies
        if (Auth::check()) {
            $companies = Company::where('user_id', Auth::user()->id)->get();
            if (isset($_GET['response']) && trim($_GET['response']) == "json") {
                return response()->json(['companies'=> $companies], 200);
            }
            return view('settings.companies', ['companies'=> $companies]);
        }
        return view('auth.login');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $company_count = Company::where('user_id', Auth::user()->id)->count();
        if ($company_count < 6) {
            return view('settings.company.add');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
        'name' => 'required|unique:companies|max:255',
        'publish_at' => 'nullable|date',
    ]);
        if (Auth::check()) {
            $company_count = Company::where('user_id', Auth::user()->id)->count();
            if ($company_count < 6) {
                $company = Company::create([
              'name' => $request->input('name'),
              'description' => $request->input('description'),
              'user_id' => Auth::user()->id
          ]);
                if ($company) {
                    // Relate the company with users and seeif the company is new
                    $companyUser = CompanyUser::where('user_id', Auth::user()->id)
                                ->where('company_id', $company->id)
                                ->first();
                    if (!$companyUser) {
                        $company->users()->attach(Auth::user()->id);
                    }
                    return redirect()->route('companies.show', ['company'=> $company->id])
              ->with('success', 'Company created successfully');
                }
            } else {
                return back()->withInput()->with('errors', 'You can not create more companies.');
            }
        }
        return back()->withInput()->with('errors', 'Error creating new company');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        $gtdftstr = UserDefaultStore::where('user_id', Auth::user()->id)
                          ->first();
        if ($gtdftstr) {
            $default_store = $gtdftstr->store_id;
        } else {
            $default_store = "";
        }
        $companiesStores = Store::where('user_id', Auth::user()->id)
                        ->where('company_id', $company->id)
                        ->get();
        $company = Company::find($company->id);
        if (isset($_GET['response']) && trim($_GET['response']) == "json") {
            return response()->json(['company'=>$company, 'companies_stores' => $companiesStores, 'default_store' => $default_store], 200);
        }
        $companies = Company::where('user_id', Auth::user()->id)->get();
        return view('settings.company.company-settings', [ 'companies' => $companies, 'company'=>$company, 'sites' => $companiesStores, 'default_store' => $default_store]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        $company = Company::find($company->id);
        return view('companies.edit', ['company'=>$company]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        if ($request->input('name') == "") {
            $request->validate([
              'name' => 'required'
            ]);
        } else {
            if ($company->name != $request->input('name')) {
                $request->validate([
                  'name' => 'unique:companies'
                ]);
            }
        }
        $companyUpdate = Company::where('id', $company->id)
                            ->update([
                                    'name'=> $request->input('name'),
                                    'description'=> $request->input('description')
                            ]);

        if ($companyUpdate) {
            return redirect()
                            ->route('companies.show', ['company'=> $company->id])
                            ->with('success', 'Company updated successfully');
        }
        //redirect
        return back()->withInput();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        //
    }
}
