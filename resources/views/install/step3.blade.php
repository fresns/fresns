<!doctype html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Fresns" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fresns &rsaquo; @lang('install.title')</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/css/bootstrap-icons.css">
</head>

<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <div class="navbar-brand">
                    <img src="/static/images/fresns-logo.png" alt="Fresns" height="30" class="d-inline-block align-text-top">
                    <span class="ms-2">@lang('install.desc')</span>
                </div>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="card mx-auto my-5" style="max-width:800px;">
            <div class="card-body p-lg-5">
                <h3 class="card-title">@lang('install.step3Title')</h3>
                <p class="mt-2">@lang('install.step3Desc')</p>
                <form class="my-4">
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">@lang('install.step3BackendHost')</label>
                        <div class="col-sm-9"><input type="url" name="backend_host" class="form-control" placeholder="https://fresns.org" required></div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">@lang('install.step3MemberNickname')</label>
                        <div class="col-sm-9"><input type="text" name="nickname" class="form-control" required></div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">@lang('install.step3AccountEmail')</label>
                        <div class="col-sm-9"><input type="email" name="email" class="form-control"></div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">@lang('install.step3AccountPhoneNumber')</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <select class="form-select" name="country_code">
                                    <option disabled="">Country Calling Codes</option>
                                    <option value="1">+1</option><option value="7">+7</option><option value="20">+20</option><option value="27">+27</option><option value="30">+30</option><option value="31">+31</option><option value="32">+32</option><option value="33">+33</option><option value="34">+34</option><option value="36">+36</option><option value="39">+39</option><option value="40">+40</option><option value="41">+41</option><option value="43">+43</option><option value="44">+44</option><option value="45">+45</option><option value="46">+46</option><option value="47">+47</option><option value="48">+48</option><option value="49">+49</option><option value="51">+51</option><option value="52">+52</option><option value="53">+53</option><option value="54">+54</option><option value="55">+55</option><option value="56">+56</option><option value="57">+57</option><option value="58">+58</option><option value="60">+60</option><option value="61">+61</option><option value="62">+62</option><option value="63">+63</option><option value="64">+64</option><option value="65">+65</option><option value="66">+66</option><option value="81">+81</option><option value="82">+82</option><option value="84">+84</option><option selected="" value="86">+86</option><option value="90">+90</option><option value="91">+91</option><option value="92">+92</option><option value="93">+93</option><option value="94">+94</option><option value="95">+95</option><option value="98">+98</option><option value="211">+211</option><option value="212">+212</option><option value="213">+213</option><option value="216">+216</option><option value="218">+218</option><option value="220">+220</option><option value="221">+221</option><option value="222">+222</option><option value="223">+223</option><option value="224">+224</option><option value="225">+225</option><option value="226">+226</option><option value="227">+227</option><option value="228">+228</option><option value="229">+229</option><option value="230">+230</option><option value="231">+231</option><option value="232">+232</option><option value="233">+233</option><option value="234">+234</option><option value="235">+235</option><option value="236">+236</option><option value="237">+237</option><option value="238">+238</option><option value="239">+239</option><option value="240">+240</option><option value="241">+241</option><option value="242">+242</option><option value="243">+243</option><option value="244">+244</option><option value="245">+245</option><option value="247">+247</option><option value="248">+248</option><option value="249">+249</option><option value="250">+250</option><option value="251">+251</option><option value="252">+252</option><option value="253">+253</option><option value="254">+254</option><option value="255">+255</option><option value="256">+256</option><option value="257">+257</option><option value="258">+258</option><option value="260">+260</option><option value="261">+261</option><option value="262">+262</option><option value="263">+263</option><option value="264">+264</option><option value="265">+265</option><option value="266">+266</option><option value="267">+267</option><option value="268">+268</option><option value="269">+269</option><option value="297">+297</option><option value="298">+298</option><option value="299">+299</option><option value="350">+350</option><option value="351">+351</option><option value="352">+352</option><option value="353">+353</option><option value="354">+354</option><option value="355">+355</option><option value="356">+356</option><option value="357">+357</option><option value="358">+358</option><option value="359">+359</option><option value="370">+370</option><option value="371">+371</option><option value="372">+372</option><option value="373">+373</option><option value="374">+374</option><option value="375">+375</option><option value="376">+376</option><option value="377">+377</option><option value="378">+378</option><option value="380">+380</option><option value="381">+381</option><option value="382">+382</option><option value="385">+385</option><option value="386">+386</option><option value="387">+387</option><option value="389">+389</option><option value="420">+420</option><option value="421">+421</option><option value="423">+423</option><option value="501">+501</option><option value="502">+502</option><option value="503">+503</option><option value="504">+504</option><option value="505">+505</option><option value="506">+506</option><option value="507">+507</option><option value="508">+508</option><option value="509">+509</option><option value="590">+590</option><option value="591">+591</option><option value="592">+592</option><option value="593">+593</option><option value="594">+594</option><option value="595">+595</option><option value="596">+596</option><option value="597">+597</option><option value="598">+598</option><option value="599">+599</option><option value="670">+670</option><option value="673">+673</option><option value="675">+675</option><option value="676">+676</option><option value="677">+677</option><option value="678">+678</option><option value="679">+679</option><option value="680">+680</option><option value="682">+682</option><option value="685">+685</option><option value="686">+686</option><option value="687">+687</option><option value="689">+689</option><option value="852">+852</option><option value="853">+853</option><option value="855">+855</option><option value="856">+856</option><option value="880">+880</option><option value="886">+886</option><option value="960">+960</option><option value="961">+961</option><option value="962">+962</option><option value="963">+963</option><option value="964">+964</option><option value="965">+965</option><option value="966">+966</option><option value="967">+967</option><option value="968">+968</option><option value="970">+970</option><option value="971">+971</option><option value="972">+972</option><option value="973">+973</option><option value="974">+974</option><option value="975">+975</option><option value="976">+976</option><option value="977">+977</option><option value="992">+992</option><option value="993">+993</option><option value="994">+994</option><option value="995">+995</option><option value="996">+996</option><option value="998">+998</option><option value="1242">+1242</option><option value="1246">+1246</option><option value="1264">+1264</option><option value="1268">+1268</option><option value="1345">+1345</option><option value="1441">+1441</option><option value="1473">+1473</option><option value="1649">+1649</option><option value="1664">+1664</option><option value="1671">+1671</option><option value="1684">+1684</option><option value="1721">+1721</option><option value="1758">+1758</option><option value="1767">+1767</option><option value="1784">+1784</option><option value="1787">+1787</option><option value="1809">+1809</option><option value="1868">+1868</option><option value="1869">+1869</option><option value="1876">+1876</option>
                                </select>
                                <input type="number" name="pure_phone" class="form-control w-75" >
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">@lang('install.step3AccountPassword')</label>
                        <div class="col-sm-9"><input type="text" name="password" class="form-control" required></div>
                    </div>
                    <div class="alert alert-danger" role="alert" id="install_error_msg" style="display: none;"></div>
                    <div class="row mt-4">
                        <label class="col-sm-3 col-form-label"></label>
                        <div class="col-sm-9">
                            <input type="hidden" id="install_submit" value="{{ route('install.manage') }}" >
                            <input type="hidden" id="install_next" value="{{ route('install.done') }}" >
                            <button type="button" id="submit" class="btn btn-outline-primary">@lang('install.step3Btn')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="/static/js/bootstrap.bundle.min.js"></script>
    <script src="/static/js/jquery-3.6.0.min.js"></script>
    <script>
        $("#submit").click(function() {
            var backend_host = $.trim($('input[name="backend_host"]').val());
            var nickname = $.trim($('input[name="nickname"]').val());
            var email = $.trim($('input[name="email"]').val());
            var country_code = $.trim($('select[name="country_code"]').val());
            var pure_phone = $.trim($('input[name="pure_phone"]').val());
            var password = $.trim($('input[name="password"]').val());
            if(backend_host == ''){
                alert('Backend host input must not empty');
                return false;
            }
            if(nickname ==''){
                alert('Nickname input must not empty');
                return false;
            }
            if(country_code == ''){
                alert('Country code input must not empty');
                return false;
            }
            if(pure_phone == '' && email == ''){
                alert('Email or Phone must input at least one');
                return false;
            }
            if(password == ''){
                alert('Password input must not empty');
                return false;
            }

            var submit_url = $('#install_submit').val();
            var next_url = $('#install_next').val();
            $.ajax({
                async: false,
                type: "post",
                url: submit_url,
                data: {
                    'backend_host': backend_host,
                    'nickname': nickname,
                    'email': email,
                    'country_code': country_code,
                    'pure_phone': pure_phone,
                    'password': password,
                },
                beforeSend: function(request) {
                    return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
                },
                success: function(data) {
                    if (data.code == '000000') {
                        $('#install_error_msg').hide();
                        location.href = next_url;
                    } else {
                        $('#install_error_msg').text(data.message).show();
                    }
                }
            })
        });
    </script>
</body>
</html>
