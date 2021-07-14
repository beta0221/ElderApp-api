
@if (isset($noFooter) && $noFooter == 1)
            {{-- no footer --}}
@else
<?php 
    $noFooter = 0;
?>
    
@endif

<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center">

        <?php

            $min = 1;
            $max = $totalPage;

            $hasPrevDot = false;
            $hasNextDot = false;

            $maxPageInRow = 5;
            $midRange = (($maxPageInRow - 1) / 2 + 1);

            if($totalPage > $maxPageInRow){
                if($page > (($maxPageInRow - 1) / 2 + 1) ){

                    if($page >= $totalPage - $midRange){
                        $min = $totalPage - ($midRange + 1);
                    }else{
                        $min = $page - ($midRange - 1);
                    }
                    $hasPrevDot = true;
                }

                if($page <= $totalPage - $midRange ){
                    if($page < $midRange){
                        $max = $maxPageInRow;
                    }else{
                        $max = $page + ($midRange - 1);
                    }
                    $hasNextDot = true;
                }
            }
        ?>

        @if ($page != 1)
        <li class="page-item">
            <a class="page-link" href="{{$url}}?page={{$page-1}}&noFooter={{$noFooter}}" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
                <span class="sr-only">Previous</span>
            </a>
        </li>      
        @endif
      

        @if ($hasPrevDot)
            <li class="ml-1 mr-1">...</li>
        @endif


        @for($i = $min;$i <= $max;$i++)
            <li class="page-item {{($i==$page)?'active':''}}">
                <a class="page-link" href="{{$url}}?page={{$i}}&noFooter={{$noFooter}}">{{$i}}</a>
            </li>    
        @endfor

        @if ($hasNextDot)
            <li class="ml-1 mr-1">...</li>
        @endif

        @if ($page != $totalPage && $totalPage != 0)
        <li class="page-item">
            <a class="page-link" href="{{$url}}?page={{$page+1}}&noFooter={{$noFooter}}" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                <span class="sr-only">Next</span>
            </a>
        </li>      
        @endif
      


    </ul>
</nav>