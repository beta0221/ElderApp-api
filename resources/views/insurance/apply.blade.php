<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>保險申請表</title>
    <style>
        body{
            padding:0 8px 24px 8px;
        }
        h4{
            margin:8px 0;
        }
        hr{
            margin: 16px 0;
        }
        table{
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table td{
            padding: 4px 8px;
            border:1px solid #000;
        }
        input{
            padding: 4px 8px;
            border-radius: .2rem;
            border: 1px solid gray;
            font-size: 12pt;
        }
        .submit-button{
            padding: 8px 16px;
            border-radius: .3rem;
            color: #fff;
            background:#61b045;
            text-align: center;
            font-size: 16pt;
            margin: 16px 0;
            display: block;
            width:100%;
            border:none;
        }
        .alert{
            color: red;
            display: block;
        }
        .radio{
            transform: scale(1.5);
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <h3>110 年度桃園市銀髮族協會會員暨眷屬自費團體保險方案「參加表」</h3>

    <h4>⬤本方案限桃園市銀髮族協會會員及其眷屬</h4>
    
    <table>
        <tr>
            <td>職類一</td>
            <td>限15足歲〜80歲</td>
        </tr>
        <tr>
            <td>職類二</td>
            <td>限15足歲〜70歲</td>
        </tr>
        <tr>
            <td>職類三</td>
            <td>限15足歲〜65歲</td>
        </tr>
    </table>
    <table>
        <tr>
            <td>保 險 內 容</td>
            <td>保  額</td>
        </tr>
        <tr>
            <td>意外傷害身故、失能保險金</td>
            <td>100萬元</td>
        </tr>
        <tr>
            <td>意外傷害住院日額保險金 /最高90日</td>
            <td>500元/日</td>
        </tr>
        <tr>
            <td>骨折未住院津貼(依骨折部位日數表)</td>
            <td>最高15000元</td>
        </tr>
    </table>


    <form action="/insurance/apply" method="POST">

        {{ csrf_field() }}
        <input type="hidden" name="token" value="{{$token}}">

        <h4>會員姓名：</h4>
        <span>{{$user->name}}</span>

        
        <h4>被保險人姓名：</h4>
        @if ($errors->first('name'))
        <span  class="alert">＊此欄位必填</span>    
        @endif
        <input type="text" name="name" value="{{(old('name'))?old('name'):$user->name}}">

        <h4>身分證字號：</h4>
        @if ($errors->first('identityNumber'))
        <span  class="alert">＊此欄位必填</span>    
        @endif
        <input type="text" name="identityNumber" value="{{(old('identityNumber'))?old('identityNumber'):$user->id_number}}">

        <h4>生日：</h4>
        @if ($errors->first('birthdate'))
        <span  class="alert">＊此欄位必填</span>    
        @endif
        <input type="date" name="birthdate" value="{{(old('birthdate'))?old('birthdate'):$user->birthdate}}">

        <h4>聯絡電話：</h4>
        @if ($errors->first('phone'))
        <span  class="alert">＊此欄位必填</span>    
        @endif
        <input type="text" name="phone" value="{{(old('phone'))?old('phone'):$user->phone}}">

        <h4>工作內容：</h4>
        @if ($errors->first('occupation'))
        <span  class="alert">＊此欄位必填</span>    
        @endif
        <input type="radio" class="radio" name="occupation" value="退休" {{(old('occupation')=='退休')?'checked':''}}><span>退休</span>
        <input type="radio" class="radio" name="occupation" value="家管" {{(old('occupation')=='家管')?'checked':''}}><span>家管</span><br>
        <input type="radio" class="radio" name="occupation" value="其他" {{(old('occupation')=='其他')?'checked':''}}><span>其他</span>
        <input type="text" name="occupation_other" value="{{(old('occupation_other'))?old('occupation_other'):''}}">

        <h4>與會員關係：</h4>
        @if ($errors->first('relation'))
        <span  class="alert">＊此欄位必填</span>    
        @endif
        <input type="text" name="relation" value="{{(old('relation'))?old('relation'):''}}">

        <hr>
        <h4>⬤詢問事項</h4>
        <p>1. 被保險人是否已投保其他商業實支實付型傷害醫療保險或實支實付型醫療保險？</p>
        @if ($errors->first('q_1'))
        <span  class="alert">＊此欄位必填</span>    
        @endif
        <input type="radio" class="radio" name="q_1" value="1" {{(old('q_1')=='1')?'checked':''}}><span>是</span>
        <input type="radio" class="radio" name="q_1" value="0" {{(old('q_1')=='0')?'checked':''}}><span>否</span>

        <p>2. 被保險人是否領有身心障礙手冊或身心障礙證明（請勾選）？如勾選是者，請提供。</p>
        @if ($errors->first('q_2'))
        <span  class="alert">＊此欄位必填</span>    
        @endif
        <input type="radio" class="radio" name="q_2" value="1" {{(old('q_2')=='1')?'checked':''}}><span>是</span>
        <input type="radio" class="radio" name="q_2" value="0" {{(old('q_2')=='0')?'checked':''}}><span>否</span>

        <p>3. 被保險人目前是否受有監護宣告（請勾選）? 如勾選是者，請提供相關證明文件。</p>
        @if ($errors->first('q_3'))
        <span  class="alert">＊此欄位必填</span>    
        @endif
        <input type="radio" class="radio" name="q_3" value="1" {{(old('q_3')=='1')?'checked':''}}><span>是</span>
        <input type="radio" class="radio" name="q_3" value="0" {{(old('q_3')=='0')?'checked':''}}><span>否</span>

        <hr>

        <p style="color: red">●本健康告知書之適用對象係以本公司於投保規則之規範為依據。</p>
        <p style="color: red">●本要保書內所陳述事項均屬事實，如有為隱匿或遺漏不為說明，或為不實之說明，旺旺友聯產物保險公司得依保險法第64條之規定解除契約，保險事故發生後亦同，為保障您的權益，請務必親自填寫並確實告知。</p>

        <p>1. 過去二年內是否曾因患有下列疾病而接受醫師治療、診療或用藥？<br>
        (1)高血壓(指收縮壓140mmHG，舒張壓90mmHG以上)、狹心症、心肌梗塞、先天性心臟病、主動脈血管瘤。<br>
        (2)腦中風(腦出血、腦梗塞)、腦瘤、癲癇、智能障礙 (外表無法明顯判斷者)、精神病、巴金森氏症。<br>
        (3)癌症(惡性腫瘤)、肝硬化、尿毒、血友病。<br>
        (4)糖尿病。<br>
        (5)酒精或藥物濫用成癮、眩暈症。<br>
        (6)視網膜出血或剝離、視神經病變。<br></p>
        @if ($errors->first('q_4'))
        <span  class="alert">＊此欄位必填</span>    
        @endif
        <input type="radio" class="radio" name="q_4" value="1" {{(old('q_4')=='1')?'checked':''}}><span>是</span>
        <input type="radio" class="radio" name="q_4" value="0" {{(old('q_4')=='0')?'checked':''}}><span>否</span>

        <p>2. 目前身體機能是否有下列障害(請勾選)：<br>
        (1)失明。<br>
        (2)是否曾因眼科疾病或傷害接受眼科專科醫師治療、診療或用藥，且一目視力經矯正後，最佳矯正視力在萬國視力表○.三以下。<br>
        (3)聾。<br>
        (4)是否曾因耳部疾病或傷害接受耳鼻喉科專科醫師治療、診療或用藥，且單耳聽力喪失程度在五十分貝(dB)以上。<br>
        (5)啞。<br>
        (6)咀嚼、吞嚥或言語機能障害。<br>
        (7)四肢(含手指、足趾)缺損或畸形。</p>
        @if ($errors->first('q_5'))
        <span  class="alert">＊此欄位必填</span>    
        @endif
        <input type="radio" class="radio" name="q_5" value="1" {{(old('q_5')=='1')?'checked':''}}><span>是</span>
        <input type="radio" class="radio" name="q_5" value="0" {{(old('q_5')=='0')?'checked':''}}><span>否</span>

        <p>※上列告知事項答「是」者，請詳細說明被保險人姓名、保險公司、商品名稱、保險金額、原因、診治經過、時間、醫院名稱、治療結果、目前狀況。</p>
        <input type="text" name="description" value="{{(old('description'))?old('description'):''}}">

        <hr>
        <h4>⬤聲明事項</h4>
        <p>1.本人（被保險人）同意旺旺友聯產物保險股份有限公司（以下簡稱旺旺友聯產險公司）得蒐集、處理及利用本人相關之健康檢查、醫療及病歷個人資料。</p>
        <p>2.本人（被保險人、要保人）同意旺旺友聯產險公司將本要保書上所載本人資料轉送產、壽險公會建立電腦系統連線，並同意產、壽險公會之會員公司查詢本人在該系統之資料以作為核保及理賠之參考，但各該公司仍應依其本身之核保或理賠標準決定是否承保或理賠，不得僅以前開資料作為承保或理賠之依據。</p>
        <p>3.本人（被保險人、要保人）同意旺旺友聯保險公司就本人之個人資料，於「個人資料保護法」所規定之範圍內，有為蒐集、處理及利用之權利。</p>
        <p>4.本人（被保險人、要保人）已知悉並明瞭實支實付型傷害醫療保險或實支實付型醫療保險之受益人，申領保險金給付時須檢具醫療費用收據正本。但若被保險人已投保旺旺友聯產險公司二張以上之商業實支實付型傷害醫療保險或實支實付型醫療保險；或本人於投保時已通知旺旺友聯產險公司有投保其他商業實支實付型傷害醫療保險或實支實付型醫療保險，而保險公司仍承保者，旺旺友聯產險公司對同一保險事故仍應依各該險別條款約定負給付責任。如有重複投保而未通知旺旺友聯產險公司者，同意旺旺友聯產險公司對同一保險事故中已獲得全民健康保險或其他人身保險契約給付的部份不負給付責任。</p>
        @if ($errors->first('agree'))
        <span  class="alert">＊此欄位必填</span>    
        @endif
        <input type="checkbox" class="radio" name="agree" value="1" {{(old('agree')=='1')?'checked':''}}><span>本人同意上述各項約定事項及聲明事項</span>

        <button class="submit-button">確定送出</button>

    </form>


</body>
</html>