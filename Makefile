RB_CUSTOMER_COMMAND=@php artisan rabbitmq:consume --queue=customer --tries=3
RB_ORDER_COMMAND=@php artisan rabbitmq:consume --queue=order --tries=3

octane-start:
	@php artisan octane:start --watch --server=roadrunner --host=0.0.0.0 --rpc-port=6001 --port=8000

rb-customer:
	$(RB_CUSTOMER_COMMAND) &

rb-order:
	$(RB_ORDER_COMMAND) &

rb-stop:
	@pkill -f "$(RB_CUSTOMER_COMMAND)" && @pkill -f "$(RB_ORDER_COMMAND)"
