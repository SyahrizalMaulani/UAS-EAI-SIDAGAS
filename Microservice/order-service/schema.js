const { buildSchema } = require('graphql');

const schema = buildSchema(`
    type Order {
        id: ID!
        customer_name: String!
        item_name: String!
        quantity: Int!
        status: String!
        created_at: String!
    }

    type Query {
        getOrders: [Order]
        getOrder(id: ID!): Order
    }

    type Mutation {
        createOrder(customer_name: String!, item_name: String!, quantity: Int!): Order
        deleteOrder(id: ID!): String
    }
`);

module.exports = schema;
