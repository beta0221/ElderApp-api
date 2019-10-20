<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div class="tree">

    </div>
</body>
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
<script>
    
window.onload = function() {
    var tree = [
    {
        'b': [
            {
                'hash_2': [
                    {
                        'g': [

                        ]
                    },
                    {
                        'h': [
                            {
                                'j': [

                                ]
                            },
                            {
                                'k': [

                                ]
                            },
                        ]
                    },
                ]
            },
        ],
    },
    {
        'c': [
            {
                'e': [

                ],
            },
            {
                'f': [

                ]
            },
        ],
    },
    {
        'd': [
            {
                'hash_3': [
                    {
                        'hash_5': [
                            {
                                'l': [

                                ]
                            },
                        ]
                    },
                ]
            },
        ],
    },
    {
        'hash_1': [
            {
                'hash_4': [
                    {
                        'i': [

                        ]
                    },
                ]
            },
        ]
    },
];
var left = 0;
runTree(tree,left);
    
};

function runTree(tree,left){

    left += 100;
    for(let i of tree){
        let key = Object.keys(i)[0];
        // console.log(key);
        $('.tree').append(`
            <div style="padding-left:${left}px;height:40px;">
                <div style="display:inline-block;border:1px solid black;width:80px;text-align:center;">
                    ${key}
                </div>
            </div>
        `);

        if(i[key].length!=0){
            runTree(i[key],left);   
        }
        
    }
    

}

</script>
</html>