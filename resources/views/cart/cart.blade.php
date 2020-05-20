@extends('main')

@section('title','| 購物車')

@section('css')
<style>

</style>
@endsection

@section('content')

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col">產品</th>
                        <th scope="col"></th>
                        <th scope="col">數量</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $p)
                    <tr>
                        <td style="width: 40px">
                            <div class="btn btn-sm btn-danger">刪除</div>
                        </td>
                        <td style="width:100px">
                            <?php $img = '/images/products/' . $p->slug . '/' . $p->img ?>
                            <div style="height: 80px;width:100%">
                                <img style="height:auto;width:auto;max-height: 100%;max-width:100%" src="{{$img}}">
                            </div>
                        </td>
                        <td>
                            <p>{{$p->name}}</p>
                        </td>
                        <td style="width:120px">
                            <input type="number" class="form-control">
                            <input type="number" class="form-control">
                            <input type="number" class="form-control">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>


    <div class="row">
        <div class="col-12">
            <div class="btn btn-block btn-primary btn-lg">下一步</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form>
                <div class="form-group">
                    <label for="exampleInputEmail1">收件人</label>
                    <input type="text"" class=" form-control" placeholder="收件人">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">聯絡電話</label>
                    <input type="text"" class=" form-control" placeholder="聯絡電話">
                </div>
                <div class="form-row">
                    <div class="col-6 mb-3">
                        <label for="validationTooltip04">縣市</label>
                        <select class="custom-select" id="validationTooltip04" required>
                            <option selected disabled value="">Choose...</option>
                            <option>...</option>
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="validationTooltip04">地區</label>
                        <select class="custom-select" id="validationTooltip04" required>
                            <option selected disabled value="">Choose...</option>
                            <option>...</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="validationTooltip04">地址</label>
                        <input type="text"" class=" form-control" placeholder="地址">
                    </div>
                </div>
                <button type="submit" class="btn btn-block btn-lg btn-primary">確定送出</button>
            </form>
        </div>
    </div>

</div>


@endsection

@section('js')
<script>

</script>
@endsection