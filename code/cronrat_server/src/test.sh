for (( i=12345678; i <= 23456789; i++ ))
do
 `curl curl http://127.0.0.1:8082/rat/$i`
done
